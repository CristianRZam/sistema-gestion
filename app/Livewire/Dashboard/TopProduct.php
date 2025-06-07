<?php

namespace App\Livewire\Dashboard;

use App\Models\SaleDetail;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TopProduct extends Component
{
    public $labels = [];
    public $data = [];

    public function mount()
    {
        $resultados = SaleDetail::select(
            'products.nombre',
            DB::raw('SUM(sale_details.cantidad) as total_vendido')
        )
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.estado_venta_id', 2) // solo ventas pagadas
            ->groupBy('products.nombre')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        $this->labels = $resultados->pluck('nombre')->toArray();
        $this->data = $resultados->pluck('total_vendido')->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.top-product');
    }
}
