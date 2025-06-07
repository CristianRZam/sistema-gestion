<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use Livewire\Component;

class Lista extends Component
{
    public function render()
    {
        $compras = Purchase::with(['comprador', 'suplier', 'estadoCompra'])
            ->orderBy('fecha_compra', 'desc')
            ->get();
        return view('livewire.purchases.lista', [
            'compras' => $compras
        ]);
    }
}
