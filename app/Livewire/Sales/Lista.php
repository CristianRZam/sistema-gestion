<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;

class Lista extends Component
{
    public function render()
    {
        $ventas = Sale::with(['vendedor', 'customer', 'estadoVenta'])
            ->orderByRaw('fecha_venta IS NULL DESC') // primero las ventas con fecha null
            ->orderBy('fecha_venta', 'desc')         // luego las demás, descendente
            ->get();


        return view('livewire.sales.lista', [
            'ventas' => $ventas
        ]);
    }

}
