<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
class Register extends Component
{
    public $fecha_venta;
    public $cliente_nombre = '';

    public $total = 0;
    public $metodo_pago;
    public $productos = [];
    public $pagina = 1;
    public $porPagina = 10;


    public $producto_nombre;
    public $producto_precio;
    public $producto_cantidad;
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

    public function eliminarClienteSeleccionado()
    {
        $this->cliente_seleccionado = null;
        $this->cliente_nombre = null; // por si estás usando este campo también
    }


    public $producto_buscar = '';
    public $productosDisponibles = [];
    public bool $esGeneradoPorReserva = false;

    public function mount($id = null)
    {
        if ($id !== null) {
            $ventaModel = Sale::with(['pagos' => function ($q) {
                $q->whereNull('auditoriaFechaEliminacion');
            }])->findOrFail($id);

            // Bloquear si ya fue pagada o si ya tiene al menos un pago
            if ($ventaModel->estado_venta_id === 3 || $ventaModel->pagado || $ventaModel->pagos->isNotEmpty()) {
                session()->flash('error', 'La venta ya tiene pagos registrados y no se puede editar.');
                redirect()->route('sales');
                return;
            }

            $this->ventaId = $id;
            $this->cargarVentaExistente($id);
            $this->esGeneradoPorReserva = !is_null($ventaModel->reservation_room_id);
        }

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

        $detalles = SaleDetail::with(['product.imagenPrincipal'])
            ->where('sale_id', $id)
            ->whereNull('sale_details.auditoriaFechaEliminacion')
            ->get();


        $this->productos = $detalles->map(function ($detalle) {
            return [
                'id' => $detalle->product->id,
                'nombre' => $detalle->product->nombre,
                'precio' => $detalle->precio_unitario,
                'cantidad' => $detalle->cantidad,
                'stock' => $detalle->product->stock,
                'imagen' => $detalle->product->imagenPrincipal->imagen_url ?? null, // Agrega la imagen aquí
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

    public function eliminarProducto($index)
    {
        unset($this->productos[$index]);
        $this->productos = array_values($this->productos); // Reindexar el array
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
                'imagen' => $producto['imagen'] ?? null,
            ];

        }

        $this->calcularTotal();
    }

    public function agregarProducto($productoId)
    {
        $producto = DB::table('products as p')
            ->leftJoin('parameters as c', function ($join) {
                $join->on('p.categoria_id', '=', 'c.idParametro')
                    ->where('c.tipo', '=', 'CATEGORIA');
            })
            ->leftJoin('product_images as pi', function ($join) {
                $join->on('p.id', '=', 'pi.product_id')
                    ->where('pi.es_principal', '=', true);
            })
            ->where('p.id', $productoId)
            ->whereNull('p.auditoriaFechaEliminacion')
            ->select(
                'p.id',
                'p.codigo',
                'p.nombre',
                'p.precio',
                'p.descripcion',
                'p.stock',
                'p.categoria_id',
                'c.nombre as categoria_nombre',
                'pi.imagen_url as imagen'
            )
            ->first();

        if ($producto) {
            $producto = (array) $producto;

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
                    'imagen' => $producto['imagen'] ?? null,
                ];
            }

            $this->calcularTotal();
        }
    }


    public string $codigoEscaneadoVenta = '';

    public function agregarProductoPorCodigo()
    {
        $codigo = trim($this->codigoEscaneadoVenta);
        $this->codigoEscaneadoVenta = '';

        if (empty($codigo)) return;

        // Buscar en la BD directamente por código
        $producto = DB::table('products as p')
            ->leftJoin('parameters as c', function ($join) {
                $join->on('p.categoria_id', '=', 'c.idParametro')
                    ->where('c.tipo', '=', 'CATEGORIA');
            })
            ->leftJoin('product_images as pi', function ($join) {
                $join->on('p.id', '=', 'pi.product_id')
                    ->where('pi.es_principal', '=', true);
            })
            ->where('p.codigo', $codigo)
            ->where('p.stock', '>', 0)
            ->whereNull('p.auditoriaFechaEliminacion')
            ->select(
                'p.id',
                'p.codigo',
                'p.nombre',
                'p.precio',
                'p.descripcion',
                'p.stock',
                'p.categoria_id',
                'c.nombre as categoria_nombre',
                'pi.imagen_url as imagen'
            )
            ->first();

        if (!$producto) {
            session()->flash('error', "Producto con código {$codigo} no encontrado.");
            $this->dispatch('errorRegisterSale', ['mensaje' => "Producto con código {$codigo} no encontrado."]);
            return;
        }

        $producto = (array) $producto;

        // Buscar si ya está en el carrito
        $index = null;
        foreach ($this->productos as $i => $p) {
            if ($p['id'] === $producto['id']) {
                $index = $i;
                break;
            }
        }

        if ($index !== null) {
            if ($this->productos[$index]['cantidad'] < $producto['stock']) {
                $this->productos[$index]['cantidad']++;
            } else {
                $this->dispatch('errorRegisterSale', ['mensaje' => "Stock máximo alcanzado para {$producto['nombre']}"]);
            }
        } else {
            $this->productos[] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio_unitario' => $producto['precio'], // ✅ clave corregida
                'cantidad' => 1,
                'stock' => $producto['stock'],
                'imagen' => $producto['imagen'] ?? null,
            ];
        }

        $this->calcularTotal();
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
            $this->dispatch('errorRegisterSale', ['mensaje' => "Debe agregar al menos un producto para registrar la venta."]);
            return;
        }

        // Validar stock
        foreach ($this->productos as $producto) {
            if ($producto['cantidad'] > $producto['stock']) {
                $this->addError('stock', "El producto '{$producto['nombre']}' no tiene suficiente stock.");
                $this->dispatch('errorRegisterSale', ['mensaje' => "El producto '{$producto['nombre']}' no tiene suficiente stock."]);
                return;
            }
        }

        DB::beginTransaction();

        try {
            if ($this->ventaId) {
                // --- ACTUALIZAR VENTA EXISTENTE ---
                $venta = Sale::findOrFail($this->ventaId);
                $venta->customer_id              = $this->cliente_seleccionado['id'] ?? null;
                $venta->total                    = $this->total ?? 0;
                $venta->auditoriaFechaModificacion = Carbon::now();
                $venta->auditoriaModificadoPor     = auth()->id();
                $venta->save();

                // Obtener detalles actuales y mapear por product_id
                $detallesActuales = SaleDetail::where('sale_id', $venta->id)
                    ->whereNull('auditoriaFechaEliminacion')
                    ->get()
                    ->keyBy('product_id');
                $idsEnNuevaVenta = [];

                // Crear o actualizar detalles según el array $this->productos
                foreach ($this->productos as $producto) {
                    $idsEnNuevaVenta[] = $producto['id'];

                    if ($detallesActuales->has($producto['id'])) {
                        // Actualizar detalle existente
                        $detalle = $detallesActuales[$producto['id']];
                        $detalle->cantidad              = $producto['cantidad'];
                        $detalle->precio_unitario       = $producto['precio'];
                        $detalle->subtotal              = $producto['cantidad'] * $producto['precio'];
                        $detalle->auditoriaFechaModificacion = Carbon::now();
                        $detalle->auditoriaModificadoPor     = auth()->id();
                        $detalle->save();
                    } else {
                        // Crear nuevo detalle
                        SaleDetail::create([
                            'sale_id'             => $venta->id,
                            'product_id'          => $producto['id'],
                            'cantidad'            => $producto['cantidad'],
                            'precio_unitario'     => $producto['precio'],
                            'subtotal'            => $producto['cantidad'] * $producto['precio'],
                            'auditoriaFechaCreacion' => Carbon::now(),
                            'auditoriaCreadoPor'     => auth()->id(),
                        ]);
                    }
                }

                // --- ELIMINACIÓN LÓGICA de detalles y pivotes que ya no están ---
                foreach ($detallesActuales as $productId => $detalle) {
                    if (!in_array($productId, $idsEnNuevaVenta)) {
                        // Pivote purchase_sale_details
                        \App\Models\PurchaseSaleDetail::where('sale_detail_id', $detalle->id)
                            ->update([
                                'auditoriaFechaEliminacion' => Carbon::now(),
                                'auditoriaEliminadoPor'     => auth()->id(),
                            ]);

                        // Marca el detalle como eliminado
                        $detalle->auditoriaFechaEliminacion = Carbon::now();
                        $detalle->auditoriaEliminadoPor     = auth()->id();
                        $detalle->save();
                    }
                }

            } else {
                // --- NUEVA VENTA ---
                $venta = Sale::create([
                    'customer_id'          => $this->cliente_seleccionado['id'] ?? null,
                    'usuario_id'           => auth()->id(),
                    'total'                => $this->total ?? 0,
                    'estado_venta_id'      => 1,
                    'auditoriaFechaCreacion' => Carbon::now(),
                    'auditoriaCreadoPor'     => auth()->id(),
                ]);

                // Crear detalles
                foreach ($this->productos as $producto) {
                    SaleDetail::create([
                        'sale_id'             => $venta->id,
                        'product_id'          => $producto['id'],
                        'cantidad'            => $producto['cantidad'],
                        'precio_unitario'     => $producto['precio'],
                        'subtotal'            => $producto['cantidad'] * $producto['precio'],
                        'auditoriaFechaCreacion' => Carbon::now(),
                        'auditoriaCreadoPor'     => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            // Redirigir al pago
            return redirect()->route('sales.pay', ['venta' => $venta->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar/editar venta: ' . $e->getMessage());
            $this->addError('productos', 'Ocurrió un error al registrar la venta.');
            $this->dispatch('errorRegisterSale', ['mensaje' => "Ocurrió un error interno al registrar la venta."]);
        }
    }





    public $producto_buscar_filtro = '';

    public function getProductosDisponiblesFiltradosProperty()
    {
        $busqueda = strtolower($this->producto_buscar_filtro);

        $query = DB::table('products as p')
            ->leftJoin('parameters as c', function ($join) {
                $join->on('p.categoria_id', '=', 'c.idParametro')
                    ->where('c.tipo', '=', 'CATEGORIA');
            })
            ->leftJoin('product_images as pi', function ($join) {
                $join->on('p.id', '=', 'pi.product_id')
                    ->where('pi.es_principal', '=', true);
            })
            ->where('p.stock', '>', 0)
            ->whereNull('p.auditoriaFechaEliminacion');

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('LOWER(p.nombre) LIKE ?', ["%{$busqueda}%"])
                    ->orWhereRaw('LOWER(p.descripcion) LIKE ?', ["%{$busqueda}%"])
                    ->orWhereRaw('LOWER(c.nombre) LIKE ?', ["%{$busqueda}%"]);
            });
        }

        $total = $query->count();

        $items = $query->select(
            'p.id',
            'p.codigo',
            'p.nombre',
            'p.precio',
            'p.descripcion',
            'p.stock',
            'p.categoria_id',
            'c.nombre as categoria_nombre',
            'pi.imagen_url as imagen',
        )
            ->offset(($this->pagina - 1) * $this->porPagina)
            ->limit($this->porPagina)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        return [
            'items' => $items,
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
