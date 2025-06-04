<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;

class Lista extends Component
{
    public function render()
    {
        $ventas = Sale::with('vendedor')->with('customer')
        ->orderBy('fecha_venta', 'desc')
            ->get();



        return view('livewire.sales.lista', [
            'ventas' => $ventas
        ]);
    }
}
