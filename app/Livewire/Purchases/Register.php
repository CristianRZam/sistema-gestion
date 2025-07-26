<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Register extends Component
{
    public $fecha_compra;
    public $proveedor_nombre = '';

    public $total = 0;
    public $metodo_pago;
    public $productos = [];
    public $pagina = 1;
    public $porPagina = 10;


    public $producto_nombre;
    public $producto_precio;
    public $producto_cantidad;
    public $compraId;

    protected $listeners = [
        'proveedorSeleccionadoDesdeCompra' => 'cargarProveedorDesdeModal',
        'producto-agregado-desde-modal' => 'agregarProductoDesdeModal',
    ];
    public $proveedor_seleccionado = null;

    public function cargarProveedorDesdeModal($proveedorId)
    {
        $proveedor = Supplier::find($proveedorId);

        if ($proveedor) {
            $this->proveedor_seleccionado = [
                'id' => $proveedor->id,
                'nombre' => $proveedor->nombre,
                'dni' => $proveedor->documento,
                'direccion' => $proveedor->direccion,
            ];

            // También puedes llenar otros campos si necesitas
            $this->proveedor_nombre = $proveedor->nombre;
        }
    }

    public function eliminarProveedorSeleccionado()
    {
        $this->proveedor_seleccionado = null;
        $this->proveedor_nombre = null;
    }
    public $producto_buscar = '';
    public $productosDisponibles = [];


    public function mount($id = null)
    {
        if ($id !== null) {
            $compraModel = Purchase::findOrFail($id);

            if ($compraModel->estado_compra_id === 3 || $compraModel->estado_compra_id === 4) {
                session()->flash('error', 'La compra ya fue completada y no se puede editar.');
                redirect()->route('purchases');
                return;
            }

            $this->compraId = $id;
            $this->cargarCompraExistente($id);
        }


        $this->calcularTotal();
    }

    public function actualizarPrecioUnitario($index, $nuevoPrecio)
    {
        if (!isset($this->productos[$index])) {
            return;
        }

        // Si está vacío, no es numérico o es menor que 0, usar 0
        $precio = is_numeric($nuevoPrecio) && $nuevoPrecio >= 0
            ? floatval($nuevoPrecio)
            : 0.0;

        $this->productos[$index]['precio_unitario'] = $precio;

        $this->calcularTotal();
    }


    public function cargarCompraExistente($id)
    {
        $compra = Purchase::with(['supplier'])->findOrFail($id);


        // Cliente
        if ($compra->supplier) {
            $this->proveedor_seleccionado = [
                'id' => $compra->supplier->id,
                'nombre' => $compra->supplier->nombre,
                'dni' => $compra->supplier->documento,
                'direccion' => $compra->supplier->direccion,
            ];
            $this->proveedor_nombre = $compra->supplier->nombre;
        }

        $detalles = PurchaseDetail::with(['product.imagenPrincipal'])
            ->where('purchase_id', $id)
            ->get();


        $this->productos = $detalles->map(function ($detalle) {
            return [
                'id' => $detalle->product->id,
                'nombre' => $detalle->product->nombre,
                'precio_unitario' => $detalle->precio_unitario,
                'cantidad' => $detalle->cantidad,
                'stock' => $detalle->product->stock,
                'imagen' => $detalle->product->imagenPrincipal->imagen_url ?? null,
            ];
        })->toArray();

        $this->fecha_compra = $compra->fecha_compra;
        $this->total = $compra->total;
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
            // Sumar la cantidad sin importar el stock
            $this->productos[$foundIndex]['cantidad'] += $producto['cantidad'];
        } else {
            $this->productos[] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio_unitario' => null, // debe ingresarse manualmente
                'cantidad' => $producto['cantidad'],
                'stock' => $producto['stock'], // opcional, puedes quitarlo si no lo usas más
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
                // Aumentar cantidad sin validar stock (en compras)
                $this->productos[$foundIndex]['cantidad']++;
            } else {
                // Agregar nuevo producto al carrito de compras
                $this->productos[] = [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'precio_unitario' => null, // el usuario lo ingresará manualmente
                    'cantidad' => 1,
                    'stock' => $producto['stock'], // opcional
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
        $this->codigoEscaneadoVenta = ''; // limpiar input después de usar

        if (empty($codigo)) return;

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
            $this->dispatch('errorRegisterPurchase', ['mensaje' => "Producto con código {$codigo} no encontrado."]);
            return;
        }

        $producto = (array) $producto;

        // Verifica si ya está en el carrito
        $index = null;
        foreach ($this->productos as $i => $p) {
            if ($p['id'] === $producto['id']) {
                $index = $i;
                break;
            }
        }

        if ($index !== null) {
            $this->productos[$index]['cantidad']++;
        } else {
            $this->productos[] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio_unitario' => null, // Se debe ingresar manualmente
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
        $this->total = 0;

        foreach ($this->productos as $producto) {
            $cantidad = intval($producto['cantidad'] ?? 0);

            // Forzar a número válido (0 si no es válido)
            $precioUnitario = isset($producto['precio_unitario']) && is_numeric($producto['precio_unitario'])
                ? floatval($producto['precio_unitario'])
                : 0;

            $this->total += $cantidad * $precioUnitario;
        }
    }


    public function registrarCompra()
    {
        if (empty($this->productos)) {
            $this->addError('productos', 'Debe agregar al menos un producto para registrar la compra.');
            $this->dispatch('errorRegisterPurchase', ['mensaje' => "Debe agregar al menos un producto para registrar la compra."]);
            return;
        }

        // Validación de campos de cada producto
        foreach ($this->productos as $producto) {
            $precio = $producto['precio_unitario'] ?? null;
            $cantidad = $producto['cantidad'] ?? null;

            if (!is_numeric($precio) || $precio < 0) {
                $this->addError('productos', "El precio unitario de '{$producto['nombre']}' es inválido.");
                $this->dispatch('errorRegisterPurchase', ['mensaje' => "El precio unitario de '{$producto['nombre']}' es inválido."]);
                return;
            }

            if (!is_numeric($cantidad) || $cantidad < 1) {
                $this->addError('productos', "La cantidad del producto '{$producto['nombre']}' debe ser al menos 1.");
                $this->dispatch('errorRegisterPurchase', ['mensaje' => "La cantidad del producto '{$producto['nombre']}' debe ser al menos 1."]);
                return;
            }
        }

        DB::beginTransaction();

        try {
            if ($this->compraId) {
                // Venta existente: actualizar
                $compra = Purchase::findOrFail($this->compraId);
                $compra->supplier_id = $this->proveedor_seleccionado['id'] ?? null;
                $compra->total = $this->total ?? 0;
                $compra->auditoriaFechaModificacion = Carbon::now();
                $compra->auditoriaModificadoPor = auth()->id();
                $compra->save();

                // Obtener productos actuales del detalle
                $detallesActuales = PurchaseDetail::where('purchase_id', $compra->id)->get()->keyBy('product_id');

                $idsEnNuevaCompra = [];

                foreach ($this->productos as $producto) {
                    $idsEnNuevaCompra[] = $producto['id'];

                    if ($detallesActuales->has($producto['id'])) {
                        // Ya existe, actualizar
                        $detalle = $detallesActuales[$producto['id']];
                        $detalle->cantidad = $producto['cantidad'];
                        $detalle->stock_restante = $producto['cantidad'];
                        $detalle->precio_unitario = $producto['precio_unitario'];
                        $detalle->subtotal = $producto['cantidad'] * $producto['precio_unitario'];
                        $detalle->auditoriaFechaModificacion = Carbon::now();
                        $detalle->auditoriaModificadoPor = auth()->id();
                        $detalle->save();
                    } else {
                        // Nuevo detalle
                        PurchaseDetail::create([
                            'purchase_id' => $compra->id,
                            'product_id' => $producto['id'],
                            'cantidad' => $producto['cantidad'],
                            'stock_restante' => $producto['cantidad'],
                            'precio_unitario' => $producto['precio_unitario'],
                            'subtotal' => $producto['cantidad'] * $producto['precio_unitario'],
                            'auditoriaFechaCreacion' => Carbon::now(),
                            'auditoriaCreadoPor' => auth()->id(),
                        ]);
                    }
                }

                // Eliminar detalles que ya no están
                foreach ($detallesActuales as $productId => $detalle) {
                    if (!in_array($productId, $idsEnNuevaCompra)) {
                        $detalle->delete();
                    }
                }

            } else {
                // Nueva venta
                $compra = Purchase::create([
                    'supplier_id' => $this->proveedor_seleccionado['id'] ?? null,
                    'usuario_id' => auth()->id(),
                    'total' => $this->total ?? 0,
                    'estado_compra_id' => 1,
                    'auditoriaFechaCreacion' => Carbon::now(),
                    'auditoriaCreadoPor' => auth()->id(),
                ]);

                foreach ($this->productos as $producto) {
                    PurchaseDetail::create([
                        'purchase_id' => $compra->id,
                        'product_id' => $producto['id'],
                        'cantidad' => $producto['cantidad'],
                        'stock_restante' => $producto['cantidad'],
                        'precio_unitario' => $producto['precio_unitario'],
                        'subtotal' => $producto['cantidad'] * $producto['precio_unitario'],
                        'auditoriaFechaCreacion' => Carbon::now(),
                        'auditoriaCreadoPor' => auth()->id(),
                    ]);
                }
            }

            DB::commit();


            // COMPRA REDIRIGIR
            return redirect()->route('purchases.pay', ['compra' => $compra->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar/editar compra: ' . $e->getMessage());
            $this->addError('productos', 'Ocurrió un error al registrar la compra.'.$e->getMessage());
            $this->dispatch('errorRegisterPurchase', ['mensaje' => "Ocurrió un error al registrar la compra."]);
        }
    }


    public $producto_buscar_filtro = '';

    public function getProductosDisponiblesFiltradosProperty()
    {
        $query = DB::table('products as p')
            ->leftJoin('parameters as c', function ($join) {
                $join->on('p.categoria_id', '=', 'c.idParametro')
                    ->where('c.tipo', '=', 'CATEGORIA');
            })
            ->leftJoin('product_images as pi', function ($join) {
                $join->on('p.id', '=', 'pi.product_id')
                    ->where('pi.es_principal', '=', true);
            })
            ->whereNull('p.auditoriaFechaEliminacion');

        if (!empty($this->producto_buscar_filtro)) {
            $busqueda = '%' . strtolower($this->producto_buscar_filtro) . '%';
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('LOWER(p.nombre) LIKE ?', [$busqueda])
                    ->orWhereRaw('LOWER(p.descripcion) LIKE ?', [$busqueda])
                    ->orWhereRaw('LOWER(c.nombre) LIKE ?', [$busqueda]);
            });
        }

        $total = $query->count();

        $productos = $query
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
            ->offset(($this->pagina - 1) * $this->porPagina)
            ->limit($this->porPagina)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        return [
            'items' => $productos,
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
        return view('livewire.purchases.register', [
            'productosDisponiblesFiltrados' => $this->productosDisponiblesFiltrados,
        ]);
    }
}
