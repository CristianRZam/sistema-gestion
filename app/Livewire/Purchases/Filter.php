<?php

namespace App\Livewire\Purchases;

use App\Models\Parameter;
use App\Models\User;
use Livewire\Component;

class Filter extends Component
{
    public $fechaDesdeFiltro = '';
    public $fechaHastaFiltro = '';
    public $estadoFiltro = [];

    public $usuarioFiltro = [];

    public $estados = [];
    public $usuarios = [];

    protected $listeners = [
        'actualizarEstadosDesdeJS' => 'actualizarEstados',
        'actualizarUsuariosDesdeJS' => 'actualizarUsuarios', // 👈 Añadir este listener
    ];

    public function actualizarUsuarios($valores = [])
    {
        $this->usuarioFiltro = $valores;
    }


    public function actualizarEstados($valores = [])
    {
        $this->estadoFiltro = $valores;
    }


    public function mount()
    {
        $this->estados= Parameter::where('codigoParametro', 'ESTADO_COMPRA')->orderBY('orden')->get();
        $this->usuarios= User::all();
    }

    public function filtrar()
    {
        // Validar formato de fechas si alguna está presente
        if ($this->fechaDesdeFiltro || $this->fechaHastaFiltro) {

            // Validar formato correcto
            if (!strtotime($this->fechaDesdeFiltro) || !strtotime($this->fechaHastaFiltro)) {
                $this->dispatch('mostrarToastFechaVenta', ['mensaje' => 'Una o ambas fechas no son válidas.']);
                return;
            }

            // Validar rango correcto
            if ($this->fechaDesdeFiltro && $this->fechaHastaFiltro &&
                $this->fechaDesdeFiltro > $this->fechaHastaFiltro) {
                $this->dispatch('mostrarToastFechaVenta', ['mensaje' => 'La fecha "desde" no puede ser mayor que la fecha "hasta".']);
                return;
            }
        }

        // Si pasa la validación, emitir evento con filtros
        $this->dispatch('filtrosActualizados', [
            'fecha_desde' => $this->fechaDesdeFiltro,
            'fecha_hasta' => $this->fechaHastaFiltro,
            'estado_ids' => $this->estadoFiltro,
            'usuario_ids' => $this->usuarioFiltro,
        ]);
    }


    public function resetFiltros()
    {
        $this->reset([
            'fechaDesdeFiltro',
            'fechaHastaFiltro',
            'estadoFiltro',
            'usuarioFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'fecha_desde' => '',
            'fecha_hasta' => '',
            'estado_ids' => [],
            'usuario_ids' => [],
        ]);


        $this->dispatch('limpiarFiltros');
    }

    public function render()
    {
        return view('livewire.purchases.filter');
    }
}
