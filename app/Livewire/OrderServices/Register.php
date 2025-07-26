<?php

namespace App\Livewire\OrderServices;

use App\Models\Customer;
use App\Models\DetailOrderService;
use App\Models\OrderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Register extends Component
{
    public $fecha;
    public $cliente_nombre = '';

    public $total = 0;

    public $servicios = [];
    public $pagina = 1;
    public $porPagina = 10;


    public $servico_nombre;
    public $servicio_precio;
    public $servicio_cantidad;
    public $orderServiceId;

    protected $listeners = [
        'clienteSeleccionadoDesdeVenta' => 'cargarClienteDesdeModal',
    ];
    public $cliente_seleccionado = null;

    public function cargarClienteDesdeModal($clienteId)
    {
        $cliente = Customer::find($clienteId);

        if ($cliente) {
            $this->cliente_seleccionado = [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'dni' => $cliente->documento,
                'direccion' => $cliente->direccion,
            ];

            // También puedes llenar otros campos si necesitas
            $this->cliente_nombre = $cliente->nombre;
        }
    }

    public function eliminarClienteSeleccionado()
    {
        $this->cliente_seleccionado = null;
        $this->cliente_nombre = null; // por si estás usando este campo también
    }


    public $servicio_buscar = '';
    public $serviciosDisponibles = [];
    public bool $esGeneradoPorReserva = false;

    public function mount($id = null)
    {
        if ($id !== null) {
            $orderServiceModel = OrderService::with(['pagos' => function ($q) {
                $q->whereNull('auditoriaFechaEliminacion');
            }])->findOrFail($id);

            // Bloquear si ya fue pagada o tiene al menos un pago
            if ($orderServiceModel->estado_id == 2 || $orderServiceModel->pagos->isNotEmpty()) {
                session()->flash('error', 'La orden ya tiene pagos registrados y no se puede editar.');
                redirect()->route('order-services');
                return;
            }

            $this->orderServiceId = $id;
            $this->cargarOrdenExistente($id);
            $this->esGeneradoPorReserva = !is_null($orderServiceModel->reservation_room_id);
        }

        $this->serviciosDisponibles = DB::table('services as s')
            ->leftJoin('service_images as si', function ($join) {
                $join->on('s.id', '=', 'si.service_id')
                    ->where('si.es_principal', '=', true);
            })
            ->where('s.activo', true)
            ->whereNull('s.auditoriaFechaEliminacion')
            ->select(
                's.id',
                's.nombre',
                's.precio',
                's.descripcion',
                'si.imagen_url as imagen',
            )
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        $this->calcularTotal();
    }


    public function cargarOrdenExistente($id)
    {
        $ordenService = OrderService::with(['customer'])->findOrFail($id);


        // Cliente
        if ($ordenService->customer) {
            $this->cliente_seleccionado = [
                'id' => $ordenService->customer->id,
                'nombre' => $ordenService->customer->nombre,
                'dni' => $ordenService->customer->documento,
                'direccion' => $ordenService->customer->direccion,
            ];
            $this->cliente_nombre = $ordenService->customer->nombre;
        }

        $detalles = DetailOrderService::with(['servicio.imagenPrincipal'])
            ->where('order_service_id', $id)
            ->whereNull('auditoriaFechaEliminacion')
            ->get();



        $this->servicios = $detalles->map(function ($detalle) {
            return [
                'id' => $detalle->servicio->id,
                'nombre' => $detalle->servicio->nombre,
                'precio' => $detalle->precio_unitario,
                'cantidad' => $detalle->cantidad,
                'imagen' => $detalle->servicio->imagenPrincipal->imagen_url ?? null,
            ];
        })->toArray();
        $this->fecha = $ordenService->fecha;
        $this->total = $ordenService->total;
    }


    public function actualizarCantidad($index, $valor)
    {
        $cantidad = (int) $valor;

        if ($cantidad <= 0) {
            unset($this->servicios[$index]);
            $this->servicios = array_values($this->servicios); // Reindexar
        } else {
            $this->servicios[$index]['cantidad'] = $cantidad;
        }

        $this->calcularTotal();
    }

    public function eliminar($index)
    {
        unset($this->servicios[$index]);
        $this->servicios = array_values($this->servicios); // Reindexar el array
        $this->calcularTotal();
    }


    public function agregar($servicioId)
    {
        $servicio = collect($this->serviciosDisponibles)->firstWhere('id', $servicioId);

        if ($servicio) {
            $foundIndex = null;

            foreach ($this->servicios as $index => $p) {
                if ($p['id'] === $servicio['id']) {
                    $foundIndex = $index;
                    break;
                }
            }

            if ($foundIndex !== null) {
                $this->servicios[$foundIndex]['cantidad']++;
            } else {
                $this->servicios[] = [
                    'id' => $servicio['id'],
                    'nombre' => $servicio['nombre'],
                    'precio' => $servicio['precio'],
                    'cantidad' => 1,
                    'imagen' => $servicio['imagen'] ?? null,
                ];
            }

            $this->calcularTotal();
        }
    }


    public function updatedServicios()
    {
        $this->calcularTotal();
    }

    public function calcularTotal()
    {
        $this->total = collect($this->servicios)->sum(function ($item) {
            return $item['precio'] * $item['cantidad'];
        });
    }

    public function registrar()
    {
        if (empty($this->servicios)) {
            $this->addError('servicios', 'Debe agregar al menos un servicio para registrar la orden.');
            $this->dispatch('errorRegisterOrder', ['mensaje' => "Debe agregar al menos un servicio para registrar la orden."]);
            return;
        }


        DB::beginTransaction();

        try {
            if ($this->orderServiceId) {
                // Actualizar orden existente
                $orden = OrderService::findOrFail($this->orderServiceId);
                $orden->customer_id = $this->cliente_seleccionado['id'] ?? null;
                $orden->total = $this->total ?? 0;
                $orden->auditoriaFechaModificacion = Carbon::now();
                $orden->auditoriaModificadoPor = auth()->id();
                $orden->save();

                $detallesActuales = DetailOrderService::where('order_service_id', $orden->id)->get()->keyBy('service_id');
                $idsEnNuevaOrden = [];

                foreach ($this->servicios as $servicio) {
                    $idsEnNuevaOrden[] = $servicio['id'];

                    if ($detallesActuales->has($servicio['id'])) {
                        // Actualizar detalle existente
                        $detalle = $detallesActuales[$servicio['id']];
                        $detalle->cantidad = $servicio['cantidad'];
                        $detalle->precio_unitario = $servicio['precio'];
                        $detalle->subtotal = $servicio['cantidad'] * $servicio['precio'];
                        $detalle->auditoriaFechaModificacion = Carbon::now();
                        $detalle->auditoriaModificadoPor = auth()->id();
                        $detalle->save();
                    } else {
                        // Crear nuevo detalle
                        DetailOrderService::create([
                            'order_service_id' => $orden->id,
                            'service_id' => $servicio['id'],
                            'cantidad' => $servicio['cantidad'],
                            'precio_unitario' => $servicio['precio'],
                            'subtotal' => $servicio['cantidad'] * $servicio['precio'],
                            'auditoriaFechaCreacion' => Carbon::now(),
                            'auditoriaCreadoPor' => auth()->id(),
                        ]);
                    }
                }

                // Eliminar detalles que ya no están
                foreach ($detallesActuales as $serviceId => $detalle) {
                    if (!in_array($serviceId, $idsEnNuevaOrden)) {
                        $detalle->auditoriaFechaEliminacion = Carbon::now();
                        $detalle->auditoriaEliminadoPor = auth()->id();
                        $detalle->save();
                    }
                }


            } else {
                // Nueva venta
                $orden = OrderService::create([
                    'customer_id' => $this->cliente_seleccionado['id'] ?? null,
                    'usuario_id' => auth()->id(),
                    'fecha' => Carbon::now(),
                    'total' => $this->total ?? 0,
                    'modo_pago_id' => 1,
                    'estado_id' => 4,
                    'auditoriaFechaCreacion' => Carbon::now(),
                    'auditoriaCreadoPor' => auth()->id(),
                ]);

                foreach ($this->servicios as $servicio) {
                    DetailOrderService::create([
                        'order_service_id' => $orden->id,
                        'service_id' => $servicio['id'],
                        'cantidad' => $servicio['cantidad'],
                        'precio_unitario' => $servicio['precio'],
                        'subtotal' => $servicio['cantidad'] * $servicio['precio'],
                        'auditoriaFechaCreacion' => Carbon::now(),
                        'auditoriaCreadoPor' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('order-services.pay', ['orden' => $orden->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar/editar oden: ' . $e->getMessage());
            $this->addError('servicios', 'Ocurrió un error al registrar la orden.'. $e->getMessage());
            $this->dispatch('errorRegisterOrder', ['mensaje' => "Ocurrió un error interno al registrar la orden."]);

        }
    }

    public $servicio_buscar_filtro = '';

    public function getServiciosDisponiblesFiltradosProperty()
    {
        $query = DB::table('services as s')
            ->leftJoin('service_images as si', function ($join) {
                $join->on('s.id', '=', 'si.service_id')
                    ->where('si.es_principal', '=', true);
            })
            ->where('s.activo', true)
            ->whereNull('s.auditoriaFechaEliminacion');

        if (!empty($this->servicio_buscar_filtro)) {
            $busqueda = '%' . strtolower($this->servicio_buscar_filtro) . '%';
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('LOWER(s.nombre) LIKE ?', [$busqueda])
                    ->orWhereRaw('LOWER(s.descripcion) LIKE ?', [$busqueda]);
            });
        }

        $total = $query->count();

        $servicios = $query
            ->select(
                's.id',
                's.nombre',
                's.precio',
                's.descripcion',
                'si.imagen_url as imagen'
            )
            ->offset(($this->pagina - 1) * $this->porPagina)
            ->limit($this->porPagina)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        return [
            'items' => $servicios,
            'total' => $total,
        ];
    }


    public function irAPagina($pagina)
    {
        $this->pagina = $pagina;
    }

    public function updatedServicioBuscar()
    {
        $this->pagina = 1;
    }

    public function buscarServicio()
    {
        $this->servicio_buscar_filtro = $this->servicio_buscar;
        $this->pagina = 1;
    }

    public function render()
    {
        return view('livewire.order-services.register', [
            'serviciosDisponiblesFiltrados' => $this->serviciosDisponiblesFiltrados,
        ]);
    }
}
