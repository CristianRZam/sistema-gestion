<?php

namespace App\Livewire\Purchases;

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
        return Product::query()
            ->leftJoin('product_images as pi', function ($join) {
                $join->on('products.id', '=', 'pi.product_id')
                    ->where('pi.es_principal', '=', true);
            })
            ->where('products.id', $id)
            ->select(
                'products.id',
                'products.codigo',
                'products.nombre',
                'products.descripcion',
                'products.precio',
                'products.stock',
                'products.categoria_id',
                'pi.imagen_url as imagen'
            )
            ->firstOrFail()
            ->toArray();
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
        return view('livewire.purchases.product-detail');
    }
}
