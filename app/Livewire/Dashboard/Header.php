<?php

namespace App\Livewire\Dashboard;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Livewire\Component;

class Header extends Component
{
    public string $filtroFecha = 'hoy';
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    public $cantidadClientes;
    public $cantidadVentas;
    public $cantidadProductosVendidos;
    public $ingresosHoy;
    public $gananciasHoy;

    public $comprasHoy;

    public $valorVentaStock;

    public $capitalRealCompraStock;


    public function mount()
    {
        $this->updatedFiltroFecha($this->filtroFecha); // establece fechas iniciales y carga datos
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['fechaInicio', 'fechaFin'])) {
            $this->validarYActualizarFechas();
        }
    }

    public function updatedFiltroFecha($value)
    {
        $hoy = Carbon::today();

        switch ($value) {
            case 'hoy':
                $this->fechaInicio = $hoy->toDateString();
                $this->fechaFin = $hoy->toDateString();
                break;

            case 'semana':
                $this->fechaInicio = $hoy->copy()->startOfWeek()->toDateString();
                $this->fechaFin = $hoy->toDateString();
                break;

            case 'mes':
                $this->fechaInicio = $hoy->copy()->startOfMonth()->toDateString();
                $this->fechaFin = $hoy->toDateString();
                break;

            case 'personalizado':
                $this->fechaInicio = null;
                $this->fechaFin = null;
                break;
        }

        $this->actualizarDatos();
    }

    public function validarYActualizarFechas()
    {
        if (!$this->fechaInicio || !$this->fechaFin) {
            return;
        }

        if (Carbon::parse($this->fechaInicio)->gt(Carbon::parse($this->fechaFin))) {
            return;
        }

        $this->actualizarDatos();
    }

    public function actualizarDatos()
    {
        if (!$this->fechaInicio || !$this->fechaFin) {
            return;
        }

        $inicio = Carbon::parse($this->fechaInicio)->startOfDay();
        $fin = Carbon::parse($this->fechaFin)->endOfDay();

        $this->cantidadClientes = Customer::whereNull('auditoriaFechaEliminacion')->count();

        $this->cantidadVentas = Sale::where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereBetween('fecha_venta', [$inicio, $fin])
            ->count();

        $this->cantidadProductosVendidos = SaleDetail::whereHas('sale', function ($query) use ($inicio, $fin) {
            $query->where('estado_venta_id', 2)
                ->whereNull('auditoriaFechaEliminacion')
                ->whereBetween('fecha_venta', [$inicio, $fin]);
        })->sum('cantidad');

        $this->ingresosHoy = Sale::where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereBetween('fecha_venta', [$inicio, $fin])
            ->get()
            ->sum(fn ($venta) => $venta->total - $venta->descuento);

        $this->gananciasHoy = 0;

        $ventas = Sale::with(['detalles.purchaseDetails'])
            ->where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereBetween('fecha_venta', [$inicio, $fin])
            ->get();

        foreach ($ventas as $venta) {
            $totalSubtotal = $venta->detalles->sum(fn ($detalle) => $detalle->subtotal ?: ($detalle->cantidad * $detalle->precio_unitario));
            $descuentoTotal = $venta->descuento ?? 0;

            foreach ($venta->detalles as $detalle) {
                $precioVentaUnitario = $detalle->precio_unitario;
                $subtotalDetalle = $detalle->subtotal ?: ($detalle->cantidad * $precioVentaUnitario);

                // Descuento proporcional por detalle
                $descuentoProporcional = $totalSubtotal > 0
                    ? ($subtotalDetalle / $totalSubtotal) * $descuentoTotal
                    : 0;

                $descuentoUnitario = $detalle->cantidad > 0
                    ? $descuentoProporcional / $detalle->cantidad
                    : 0;

                foreach ($detalle->purchaseDetails as $purchaseDetail) {
                    $cantidadUtilizada = $purchaseDetail->pivot->cantidad_utilizada;
                    $precioCompra = $purchaseDetail->precio_unitario;
                    $cantidadCompra = $purchaseDetail->cantidad;

                    // Perdidas tipo 3: se devolvió el dinero pero no el producto
                    $perdidasTipo3 = $purchaseDetail->losses()
                        ->where('tipo_id', 3)
                        ->whereNull('auditoriaFechaEliminacion')
                        ->get();

                    $cantidadGratis = $perdidasTipo3->sum('cantidad_fallida');

                    // Si se usó un costo manual, respetarlo; si no, usar precioCompra
                    $costoUnitarioBase = $purchaseDetail->pivot->costo_unitario ?? $precioCompra;

                    // Calcular cuánta parte de la venta usó unidades "gratis"
                    $cantidadConCosto0 = min($cantidadUtilizada, $cantidadGratis);
                    $cantidadConCostoNormal = $cantidadUtilizada - $cantidadConCosto0;

                    // Ganancia 100% por unidades "gratis"
                    $gananciaGratis = ($precioVentaUnitario - $descuentoUnitario) * $cantidadConCosto0;

                    // Ganancia normal por unidades restantes
                    $gananciaNormal = ($precioVentaUnitario - $costoUnitarioBase - $descuentoUnitario) * $cantidadConCostoNormal;

                    // Sumar total al acumulador
                    $this->gananciasHoy += $gananciaGratis + $gananciaNormal;
                }
            }
        }



        $this->comprasHoy = 0;

        $compras = Purchase::with(['detalles.losses'])
            ->whereNull('auditoriaFechaEliminacion')
            ->whereBetween('fecha_compra', [$inicio, $fin])
            ->whereIn('estado_compra_id', [2, 3])
            ->get();

        foreach ($compras as $compra) {
            foreach ($compra->detalles as $detalle) {
                $cantidad = $detalle->cantidad;
                $precio = $detalle->precio_unitario;

                $perdidas = $detalle->losses()
                    ->whereIn('tipo_id', [1, 2, 3])
                    ->whereNull('auditoriaFechaEliminacion')
                    ->get();

                foreach ($perdidas as $perdida) {
                    if ($perdida->tipo_id == 1 || $perdida->tipo_id == 3) {
                        $cantidad -= $perdida->cantidad_fallida;
                    }
                    // tipo_id 2 (pérdida) no afecta directamente al descuento
                }

                $this->comprasHoy += max(0, $cantidad) * $precio;
            }
        }


        $this->capitalRealCompraStock = PurchaseDetail::whereNull('auditoriaFechaEliminacion')
            ->whereHas('product', fn ($q) => $q->whereNull('auditoriaFechaEliminacion'))
            ->whereHas('purchase', function ($query) {
                $query->whereNull('auditoriaFechaEliminacion')
                    ->whereIn('estado_compra_id', [2, 3]);
            })
            ->get()
            ->sum(fn ($detalle) => $detalle->stock_restante * $detalle->precio_unitario);




        $this->valorVentaStock = Product::whereNull('auditoriaFechaEliminacion')
            ->get()
            ->sum(fn ($producto) => $producto->stock * $producto->precio);


        $this->dispatch('rangoFechasActualizado', [
            'inicio' => $this->fechaInicio,
            'fin' => $this->fechaFin,
        ]);
    }


    public function getEtiquetaVentasProperty()
    {
        return match ($this->filtroFecha) {
            'hoy' => 'Ventas hoy',
            'semana' => 'Ventas de la semana',
            'mes' => 'Ventas del mes',
            default => 'Ventas',
        };
    }

    public function getEtiquetaGananciasProperty()
    {
        return match ($this->filtroFecha) {
            'hoy' => 'Ganancias hoy',
            'semana' => 'Ganancias de la semana',
            'mes' => 'Ganancias del mes',
            default => 'Ganancias',
        };
    }

    public function getEtiquetaProductosVendidosProperty()
    {
        return match ($this->filtroFecha) {
            'hoy' => 'Productos vendidos hoy',
            'semana' => 'Productos vendidos de la semana',
            'mes' => 'Productos vendidos del mes',
            default => 'Productos vendidos',
        };
    }

    public function getEtiquetaIngresosProperty()
    {
        return match ($this->filtroFecha) {
            'hoy' => 'Ingresos hoy',
            'semana' => 'Ingresos de la semana',
            'mes' => 'Ingresos del mes',
            default => 'Ingresos',
        };
    }

    public function getEtiquetaComprasProperty()
    {
        return match ($this->filtroFecha) {
            'hoy' => 'Compras hoy',
            'semana' => 'Compras de la semana',
            'mes' => 'Compras del mes',
            default => 'Compras',
        };
    }


    public function render()
    {
        return view('livewire.dashboard.header');
    }
}
