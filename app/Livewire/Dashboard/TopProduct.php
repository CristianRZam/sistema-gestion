<?php

namespace App\Livewire\Dashboard;

use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class TopProduct extends Component
{
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    public $labels = [];
    public $data = [];

    protected $listeners = ['rangoFechasActualizado' => 'actualizarRangoFechas'];

    public function mount()
    {
        $this->fechaInicio = now()->toDateString();
        $this->fechaFin = now()->toDateString();

        $this->cargarDatos();
    }

    public function actualizarRangoFechas(array $rango)
    {
        $this->fechaInicio = $rango['inicio'];
        $this->fechaFin = $rango['fin'];

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->labels = [];
        $this->data = [];

        $query = SaleDetail::select(
            'products.nombre',
            DB::raw('SUM(sale_details.cantidad) as total_vendido')
        )
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.estado_venta_id', 2); // Solo ventas pagadas

        if ($this->fechaInicio && $this->fechaFin) {
            $inicio = Carbon::parse($this->fechaInicio)->startOfDay();
            $fin = Carbon::parse($this->fechaFin)->endOfDay();

            $query->whereBetween('sales.fecha_venta', [$inicio, $fin]);
        }

        $resultados = $query->groupBy('products.nombre')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        $this->labels = $resultados->pluck('nombre')->toArray();
        $this->data = $resultados->pluck('total_vendido')->toArray();

        $this->dispatch('actualizarGraficoTopProductos', [
            'labels' => $this->labels,
            'data' => $this->data,
        ]);
    }

    public function render()
    {
        return view('livewire.dashboard.top-product');
    }
}
