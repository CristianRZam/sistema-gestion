<?php

namespace App\Livewire\Parameters;

use App\Models\Parameter;
use Livewire\Component;

class Filter extends Component
{
    public $nombreFiltro = '';
    public $tipoFiltro = [];

    public $codigoFiltro = '';

    public $tipos = [];

    protected $listeners = ['actualizarTiposDesdeJS' => 'actualizarTipos'];

    public function actualizarTipos($valores = [])
    {
        $this->tipoFiltro = $valores;
    }


    public function mount()
    {
        $this->tipos = Parameter::where('codigoParametro', 'TIPO_PARAMETRO')->orderBY('orden')->get();
    }

    public function filtrar()
    {
        $this->dispatch('filtrosActualizados', [
            'nombre' => $this->nombreFiltro,
            'tipo_ids' => $this->tipoFiltro,
            'codigo' => $this->codigoFiltro,
        ]);
    }

    public function resetFiltros()
    {
        $this->reset([
            'nombreFiltro',
            'tipoFiltro',
            'codigoFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'nombre' => '',
            'tipo_ids' => [],
            'codigo' => '',
        ]);


        $this->dispatch('limpiarFiltros');
    }

    public function render()
    {
        return view('livewire.parameters.filter');
    }
}
