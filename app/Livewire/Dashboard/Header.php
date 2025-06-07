<?php

namespace App\Livewire\Dashboard;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Livewire\Component;

class Header extends Component
{
    public $cantidadClientes;
    public $cantidadVentas;
    public $cantidadProductosVendidos;
    public $ingresosHoy;



    public function mount()
    {
        $hoy = Carbon::today();

        $this->cantidadClientes = Customer::whereNull('auditoriaFechaEliminacion')->count();

        $this->cantidadVentas = Sale::where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereDate('fecha_venta', $hoy)
            ->count();

        // Productos vendidos hoy (solo en ventas válidas)
        $this->cantidadProductosVendidos = SaleDetail::whereHas('sale', function ($query) use ($hoy) {
            $query->where('estado_venta_id', 2)
                ->whereNull('auditoriaFechaEliminacion')
                ->whereDate('fecha_venta', $hoy);
        })->sum('cantidad');

        $this->ingresosHoy = Sale::where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereDate('fecha_venta', $hoy)
            ->get()
            ->sum(function ($venta) {
                return $venta->total - $venta->descuento;
            });

    }



    public function render()
    {
        return view('livewire.dashboard.header');
    }
}
