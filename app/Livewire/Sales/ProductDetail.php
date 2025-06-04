<?php

namespace App\Livewire\Sales;

use App\Models\Product;
use Livewire\Component;

class ProductDetail extends Component
{
    public $productoSeleccionado = [];
    public $cantidadSeleccionada = 1;

    protected $listeners = ['open-modal-product-detail' => 'abrirModalProducto'];

    public function abrirModalProducto($productoId)
    {
        $producto = $this->buscarProductoPorId($productoId);
        $this->productoSeleccionado = $producto;
    }

    public function buscarProductoPorId($id)
    {
        return Product::findOrFail($id)->toArray();
    }

    public function agregarProductoConCantidad()
    {
        $producto = $this->productoSeleccionado;
        $producto['cantidad'] = $this->cantidadSeleccionada;

        $this->dispatch('producto-agregado-desde-modal', $producto);

        $this->dispatch('cerrarModalProductDetail');
    }


    public function render()
    {
        return view('livewire.sales.product-detail');
    }
}
