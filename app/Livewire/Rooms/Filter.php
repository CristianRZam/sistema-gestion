<?php

namespace App\Livewire\Rooms;

use App\Models\Parameter;
use Livewire\Component;

class Filter extends Component
{
    public $tipoFiltro = [];
    public $pisoFiltro = [];
    public $estadoFiltro = [];


    public $tipos = [];
    public $pisos = [];
    public $estados = [];

    protected $listeners = [
        'actualizarTiposDesdeJS' => 'actualizarTipos',
        'actualizarPisosDesdeJS' => 'actualizarPisos',
        'actualizarEstadosDesdeJS' => 'actualizarEstados',
    ];

    public function actualizarTipos($valores = [])
    {
        $this->tipoFiltro = $valores;
    }

    public function actualizarPisos($valores = [])
    {
        $this->pisoFiltro = $valores;
    }

    public function actualizarEstados($valores = [])
    {
        $this->estadoFiltro = $valores;
    }


    public function mount()
    {
        $this->tipos = Parameter::where('codigoParametro', 'TIPO_HABITACION')->orderBY('orden')->get();
        $this->pisos = Parameter::where('codigoParametro', 'PISO_HABITACION')->orderBY('orden')->get();
        $this->estados = Parameter::where('codigoParametro', 'ESTADO_HABITACION')->orderBY('orden')->get();
    }

    public function filtrar()
    {
        $this->dispatch('filtrosActualizados', [
            'tipo_ids' => $this->tipoFiltro,
            'piso_ids' => $this->pisoFiltro,
            'estado_ids' => $this->estadoFiltro,
        ]);
    }

    public function resetFiltros()
    {
        $this->reset([
            'tipoFiltro',
            'pisoFiltro',
            'estadoFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'tipo_ids' => [],
            'piso_ids' => [],
            'estado_ids' => [],
        ]);


        $this->dispatch('limpiarFiltros');
    }

    public function render()
    {
        return view('livewire.rooms.filter');
    }
}
