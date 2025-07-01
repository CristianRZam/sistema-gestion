<?php

namespace App\Livewire\Dashboard;

use App\Models\Customer;
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

        $ventas = Sale::with(['detalles.product'])
            ->where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereBetween('fecha_venta', [$inicio, $fin])
            ->get();

        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                $cantidadVendida = $detalle->cantidad;
                $precioVentaUnitario = $detalle->precio_unitario;

                $purchaseDetail = PurchaseDetail::where('product_id', $detalle->product_id)
                    ->whereNull('auditoriaFechaEliminacion')
                    ->latest('id')
                    ->first();

                if (!$purchaseDetail) continue;

                $precioCompra = $purchaseDetail->precio_unitario;
                $cantidadCompra = $purchaseDetail->cantidad;

                $perdidasTipo3 = $purchaseDetail->losses()
                    ->where('tipo_id', 3)
                    ->whereNull('auditoriaFechaEliminacion')
                    ->get();

                $cantidadPerdida = $perdidasTipo3->sum('cantidad_fallida');
                $montoReembolsado = $cantidadPerdida * $precioCompra;

                $nuevoCostoTotal = ($precioCompra * $cantidadCompra) - $montoReembolsado;

                $costoUnitarioAjustado = $cantidadCompra > 0
                    ? $nuevoCostoTotal / $cantidadCompra
                    : $precioCompra;

                $ganancia = ($precioVentaUnitario - $costoUnitarioAjustado) * $cantidadVendida;
                $this->gananciasHoy += $ganancia;
            }
        }

        $this->dispatch('rangoFechasActualizado', [
            'inicio' => $this->fechaInicio,
            'fin' => $this->fechaFin,
        ]);

    }

    public function render()
    {
        return view('livewire.dashboard.header');
    }
}
