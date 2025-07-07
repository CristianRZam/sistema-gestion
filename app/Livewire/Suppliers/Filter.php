<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;

class Filter extends Component
{
    public $nombreFiltro = '';
    public $numeroDocumentoFiltro = '';


    public function filtrar()
    {
        $this->dispatch('filtrosActualizados', [
            'nombre' => $this->nombreFiltro,
            'numero_documento' => $this->numeroDocumentoFiltro,
        ]);
    }

    public function resetFiltros()
    {
        $this->reset([
            'nombreFiltro',
            'numeroDocumentoFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'nombre' => '',
            'numero_documento' => '',
        ]);

        $this->dispatch('limpiarFiltros');
    }

    public function render()
    {
        return view('livewire.suppliers.filter');
    }
}
