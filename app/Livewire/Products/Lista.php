<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Lista extends Component
{
    public $productos;
    public $productIdToDelete;

    protected $listeners = [
        'actualiza-lista-producto' => 'actualizarProductos',
        'open-modal-delete-product' => 'setProductIdToDelete',
    ];

    public function actualizarProductos()
    {
        $this->productos = Product::whereNull('auditoriaFechaEliminacion')->get();
    }

    public function render()
    {
        $this->actualizarProductos();

        return view('livewire.products.lista', [
            'productos' => $this->productos,
        ]);
    }

    public function setProductIdToDelete($id = null)
    {
        $this->productIdToDelete = $id;
    }

    public function deleteProduct()
    {
        if (!$this->productIdToDelete) {
            session()->flash('error', 'Producto no válido.');
            return;
        }

        $product = Product::find($this->productIdToDelete);

        if (!$product) {
            session()->flash('error', 'Producto no encontrado.');
            return;
        }

        $product->update([
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor' => Auth::id(),
        ]);

        $this->dispatch('actualiza-lista-producto');
        $this->dispatch('cerrarModalDeteleProduct');
        $this->reset('productIdToDelete');
    }
}
