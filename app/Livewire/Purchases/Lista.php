<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use Livewire\Component;

class Lista extends Component
{
    public function render()
    {
        $compras = Purchase::with(['comprador', 'supplier', 'estadoCompra'])
            ->orderByRaw('fecha_compra IS NULL DESC') // primero nulls
            ->orderBy('fecha_compra', 'desc')         // luego los demás descendente
            ->get();

        return view('livewire.purchases.lista', [
            'compras' => $compras
        ]);
    }

}
