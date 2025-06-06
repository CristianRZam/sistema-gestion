<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
class Register extends Component
{
    public $fecha_venta;
    public $cliente_nombre = '';
    public $clientes_sugeridos = [];
    public $total = 0;
    public $metodo_pago;
    public $productos = [];
    public $pagina = 1;
    public $porPagina = 10;


    public $producto_nombre;
    public $producto_precio;
    public $producto_cantidad;
    public $mostrar_modal_producto = false;
    public $ventaId;

    protected $listeners = [
        'clienteSeleccionadoDesdeVenta' => 'cargarClienteDesdeModal',
        'producto-agregado-desde-modal' => 'agregarProductoDesdeModal',
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

    public $producto_buscar = '';
    public $productosDisponibles = [];


    public function mount($id = null)
    {
        if ($id !== null) {
            $ventaModel = Sale::findOrFail($id);

            // 🔒 Validar si ya está pagada
            if ($ventaModel->estado_venta_id != 1) {
                session()->flash('error', 'La venta ya fue pagada y no se puede editar.');
                redirect()->route('sales');
                return;
            }

            $this->ventaId = $id;

            $this->cargarVentaExistente($id); // Carga los datos de la venta existente
        }

        // Productos disponibles se cargan siempre
        $this->productosDisponibles = DB::table('products as p')
            ->leftJoin('parameters as c', function ($join) {
                $join->on('p.categoria_id', '=', 'c.idParametro')
                    ->where('c.tipo', '=', 'CATEGORIA');
            })
            ->select(
                'p.id',
                'p.nombre',
                'p.precio',
                'p.descripcion',
                'p.stock',
                'p.categoria_id',
                'c.nombre as categoria_nombre'
            )
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        $this->calcularTotal();
    }

    public function cargarVentaExistente($id)
    {
        $venta = Sale::with(['customer'])->findOrFail($id);


        // Cliente
        if ($venta->customer) {
            $this->cliente_seleccionado = [
                'id' => $venta->customer->id,
                'nombre' => $venta->customer->nombre,
                'dni' => $venta->customer->documento,
                'direccion' => $venta->customer->direccion,
            ];
            $this->cliente_nombre = $venta->customer->nombre;
        }

        $detalles = SaleDetail::with('product')->where('sale_id', $id)->get();

        $this->productos = $detalles->map(function ($detalle) {
            return [
                'id' => $detalle->product->id,
                'nombre' => $detalle->product->nombre,
                'precio' => $detalle->precio_unitario,
                'cantidad' => $detalle->cantidad,
                'stock' => $detalle->product->stock,
            ];
        })->toArray();
        $this->fecha_venta = $venta->fecha_venta;
        $this->total = $venta->total;
    }




    public function actualizarCantidad($index, $valor)
    {
        $cantidad = (int) $valor;

        if ($cantidad <= 0) {
            unset($this->productos[$index]);
            $this->productos = array_values($this->productos); // Reindexar
        } else {
            $this->productos[$index]['cantidad'] = $cantidad;
        }

        $this->calcularTotal();
    }


    public function agregarProductoDesdeModal($producto)
    {
        $foundIndex = null;

        foreach ($this->productos as $index => $p) {
            if ($p['id'] === $producto['id']) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex !== null) {
            $nuevaCantidad = $this->productos[$foundIndex]['cantidad'] + $producto['cantidad'];

            if ($nuevaCantidad <= $producto['stock']) {
                $this->productos[$foundIndex]['cantidad'] = $nuevaCantidad;
            } else {
                $this->productos[$foundIndex]['cantidad'] = $producto['stock'];
            }
        } else {
            $this->productos[] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => min($producto['cantidad'], $producto['stock']),
                'stock' => $producto['stock'],
            ];
        }

        $this->calcularTotal();
    }

    public function agregarProducto($productoId)
    {
        $producto = collect($this->productosDisponibles)->firstWhere('id', $productoId);

        if ($producto) {
            $foundIndex = null;

            foreach ($this->productos as $index => $p) {
                if ($p['id'] === $producto['id']) {
                    $foundIndex = $index;
                    break;
                }
            }

            if ($foundIndex !== null) {
                if ($this->productos[$foundIndex]['cantidad'] < $producto['stock']) {
                    $this->productos[$foundIndex]['cantidad']++;
                }
            } else {
                $this->productos[] = [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio'],
                    'cantidad' => 1,
                    'stock' => $producto['stock'],
                ];
            }

            $this->calcularTotal();
        }
    }



    public function updatedProductos()
    {
        $this->calcularTotal();
    }

    public function calcularTotal()
    {
        $this->total = collect($this->productos)->sum(function ($item) {
            return $item['precio'] * $item['cantidad'];
        });
    }

    public function registrarVenta()
    {
        if (empty($this->productos)) {
            $this->addError('productos', 'Debe agregar al menos un producto para registrar la venta.');
            return;
        }

        foreach ($this->productos as $producto) {
            if ($producto['cantidad'] > $producto['stock']) {
                $this->addError('stock', "El producto '{$producto['nombre']}' no tiene suficiente stock.");
                return;
            }
        }

        DB::beginTransaction();

        try {
            if ($this->ventaId) {
                // Venta existente: actualizar
                $venta = Sale::findOrFail($this->ventaId);
                $venta->customer_id = $this->cliente_seleccionado['id'] ?? null;
                $venta->total = $this->total ?? 0;
                $venta->auditoriaFechaModificacion = Carbon::now();
                $venta->auditoriaModificadoPor = auth()->id();
                $venta->save();

                // Obtener productos actuales del detalle
                $detallesActuales = SaleDetail::where('sale_id', $venta->id)->get()->keyBy('product_id');

                $idsEnNuevaVenta = [];

                foreach ($this->productos as $producto) {
                    $idsEnNuevaVenta[] = $producto['id'];

                    if ($detallesActuales->has($producto['id'])) {
                        // Ya existe, actualizar
                        $detalle = $detallesActuales[$producto['id']];
                        $detalle->cantidad = $producto['cantidad'];
                        $detalle->precio_unitario = $producto['precio'];
                        $detalle->subtotal = $producto['cantidad'] * $producto['precio'];
                        $detalle->auditoriaFechaModificacion = Carbon::now();
                        $detalle->auditoriaModificadoPor = auth()->id();
                        $detalle->save();
                    } else {
                        // Nuevo detalle
                        SaleDetail::create([
                            'sale_id' => $venta->id,
                            'product_id' => $producto['id'],
                            'cantidad' => $producto['cantidad'],
                            'precio_unitario' => $producto['precio'],
                            'subtotal' => $producto['cantidad'] * $producto['precio'],
                            'auditoriaFechaCreacion' => Carbon::now(),
                            'auditoriaCreadoPor' => auth()->id(),
                        ]);
                    }
                }

                // Eliminar detalles que ya no están
                foreach ($detallesActuales as $productId => $detalle) {
                    if (!in_array($productId, $idsEnNuevaVenta)) {
                        $detalle->delete();
                    }
                }

            } else {
                // Nueva venta
                $venta = Sale::create([
                    'customer_id' => $this->cliente_seleccionado['id'] ?? null,
                    'usuario_id' => auth()->id(),
                    'fecha_venta' => Carbon::now(),
                    'total' => $this->total ?? 0,
                    'estado_venta_id' => 1,
                    'auditoriaFechaCreacion' => Carbon::now(),
                    'auditoriaCreadoPor' => auth()->id(),
                ]);

                foreach ($this->productos as $producto) {
                    SaleDetail::create([
                        'sale_id' => $venta->id,
                        'product_id' => $producto['id'],
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario' => $producto['precio'],
                        'subtotal' => $producto['cantidad'] * $producto['precio'],
                        'auditoriaFechaCreacion' => Carbon::now(),
                        'auditoriaCreadoPor' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sales.pay', ['venta' => $venta->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar/editar venta: ' . $e->getMessage());
            $this->addError('productos', 'Ocurrió un error al registrar la venta.');
        }
    }


    public $producto_buscar_filtro = '';

    public function getProductosDisponiblesFiltradosProperty()
    {
        $busqueda = strtolower($this->producto_buscar_filtro);

        $productosFiltrados = empty($busqueda)
            ? collect($this->productosDisponibles)
            : collect($this->productosDisponibles)->filter(function ($p) use ($busqueda) {
                // Convertir todos los campos a texto y comparar
                $nombre = strtolower($p['nombre'] ?? '');
                $descripcion = strtolower($p['descripcion'] ?? '');
                $categoria = strtolower($p['categoria_nombre'] ?? ''); // ← Aquí la corrección

                return str_contains($nombre, $busqueda)
                    || str_contains($descripcion, $busqueda)
                    || str_contains($categoria, $busqueda);
            });

        $total = $productosFiltrados->count();
        $inicio = ($this->pagina - 1) * $this->porPagina;

        return [
            'items' => $productosFiltrados->slice($inicio, $this->porPagina)->values()->all(),
            'total' => $total,
        ];
    }




    public function irAPagina($pagina)
    {
        $this->pagina = $pagina;
    }

    public function updatedProductoBuscar()
    {
        $this->pagina = 1;
    }

    public function buscarProducto()
    {
        $this->producto_buscar_filtro = $this->producto_buscar;
        $this->pagina = 1;
    }


    public function render()
    {
        return view('livewire.sales.register', [
            'productosDisponiblesFiltrados' => $this->productosDisponiblesFiltrados,
        ]);
    }

}
