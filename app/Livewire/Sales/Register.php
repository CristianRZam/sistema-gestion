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


    public function mount()
    {
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

        // Verificar que hay al menos un producto agregado
        if (empty($this->productos)) {
            $this->addError('productos', 'Debe agregar al menos un producto para registrar la venta.');
            return;
        }

        // Validar que todos los productos tengan suficiente stock
        foreach ($this->productos as $producto) {
            if ($producto['cantidad'] > $producto['stock']) {
                $this->addError('stock', "El producto '{$producto['nombre']}' no tiene suficiente stock.");
                return;
            }
        }

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

            // (Opcional) Actualizar el stock del producto
            // Product::where('id', $producto['id'])->decrement('stock', $producto['cantidad']);
        }

        return redirect()->route('sales.pay', ['venta' => $venta->id]);
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
