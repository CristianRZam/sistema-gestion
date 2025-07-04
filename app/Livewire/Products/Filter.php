<?php

namespace App\Livewire\Products;

use App\Models\Parameter;
use Livewire\Component;

class Filter extends Component
{
    public $nombreFiltro = '';
    public $categoriaFiltro = [];

    public $stockFiltro = '';

    public $categorias = [];

    protected $listeners = ['actualizarCategoriasDesdeJS' => 'actualizarCategorias'];

    public function actualizarCategorias($valores = [])
    {
        $this->categoriaFiltro = $valores;
    }


    public function mount()
    {
        $this->categorias = Parameter::where('codigoParametro', 'CATEGORIA')->orderBY('orden')->get();
    }

    public function filtrar()
    {
        $this->dispatch('filtrosActualizados', [
            'nombre' => $this->nombreFiltro,
            'categoria_ids' => $this->categoriaFiltro,
            'stock' => $this->stockFiltro,
        ]);
    }

    public function resetFiltros()
    {
        $this->reset([
            'nombreFiltro',
            'categoriaFiltro',
            'stockFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'nombre' => '',
            'categoria_id' => '',
            'stock' => '',
        ]);

        $this->dispatch('limpiarFiltros');
    }

    public function render()
    {
        return view('livewire.products.filter');
    }
}
