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
    public $modoContinuo = false; // Nuevo: Modo escaneo continuo
    public $codigoEscaneado = '';

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

    public function procesarCodigoEscaneado($codigo)
    {
        $this->codigoEscaneado = $codigo;

        $producto = Product::where('codigo', $codigo)->first();

        if (!$producto) {
            session()->flash('error', 'Producto no encontrado.');
            $this->dispatch('open-modal-product');
            return;
        }

        if ($this->modoContinuo) {
            // Agregar stock de 1 directamente
            $producto->stock += 1;
            $producto->save();
            session()->flash('success', 'Stock actualizado automáticamente.');
            $this->dispatch('actualiza-lista-producto');
        } else {
            // Abrir modal como si fuera edición
            $this->dispatch('open-modal-product', ['id' => $producto->id]);
        }
    }

    public function toggleModoEscaneoContinuo()
    {
        $this->modoContinuo = !$this->modoContinuo;
    }

}
