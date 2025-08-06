<?php

namespace App\Livewire\Tables;

use Livewire\Component;

class Filter extends Component
{
    public $codigoFiltro = '';

    public $estadoFiltro = '';

    public function filtrar()
    {
        $this->dispatch('filtrosActualizados', [
            'codigo' => $this->codigoFiltro,
            'estado' => $this->estadoFiltro,
        ]);
    }

    public function resetFiltros()
    {
        $this->reset([
            'codigoFiltro',
            'estadoFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'codigo' => '',
            'estado' => '',
        ]);


        $this->dispatch('limpiarFiltros');
    }

    public function render()
    {
        return view('livewire.tables.filter');
    }
}
