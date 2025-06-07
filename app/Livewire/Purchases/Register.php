<?php

namespace App\Livewire\Purchases;

use Livewire\Component;

class Register extends Component
{
    public $fecha_compra;
    public $proveedor_nombre = '';
    public $proveedores_sugeridos = [];
    public $total = 0;
    public $metodo_pago;
    public $productos = [];
    public $pagina = 1;
    public $porPagina = 10;


    public $producto_nombre;
    public $producto_precio;
    public $producto_cantidad;
    public $mostrar_modal_producto = false;
    public $ventaId;

    protected $listeners = [
        'proveedorSeleccionadoDesdeVenta' => 'cargarProveedorDesdeModal',
        'producto-agregado-desde-modal' => 'agregarProductoDesdeModal',
    ];
    public $proveedor_seleccionado = null;
    public function render()
    {
        return view('livewire.purchases.register');
    }
}
