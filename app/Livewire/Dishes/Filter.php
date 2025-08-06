<?php

namespace App\Livewire\Dishes;

use App\Models\Parameter;
use Livewire\Component;

class Filter extends Component
{
    public $nombreFiltro = '';
    public $categoriaFiltro = [];

    public $estadoFiltro = '';

    public $categorias = [];

    protected $listeners = ['actualizarCategoriasDesdeJS' => 'actualizarCategorias'];

    public function actualizarCategorias($valores = [])
    {
        $this->categoriaFiltro = $valores;
    }


    public function mount()
    {
        $this->categorias = Parameter::where('codigoParametro', 'CATEGORIA_PLATILLO')->orderBY('orden')->get();
    }

    public function filtrar()
    {
        $this->dispatch('filtrosActualizados', [
            'nombre' => $this->nombreFiltro,
            'categoria_ids' => $this->categoriaFiltro,
            'estado' => $this->estadoFiltro,
        ]);
    }

    public function resetFiltros()
    {
        $this->reset([
            'nombreFiltro',
            'categoriaFiltro',
            'estadoFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'nombre' => '',
            'categoria_ids' => [],
            'estado' => '',
        ]);


        $this->dispatch('limpiarFiltros');
    }
    public function render()
    {
        return view('livewire.dishes.filter');
    }
}
