<?php

namespace App\Livewire\Reservations;

use Carbon\Carbon;
use Livewire\Component;

class FilterRoomSelector extends Component
{
    public $fechaDesdeFiltro = '';
    public $fechaHastaFiltro = '';

    public function mount()
    {
        $hoy = Carbon::today()->format('Y-m-d');
        $this->fechaDesdeFiltro = $hoy;
        $this->fechaHastaFiltro = $hoy;
    }

    public function filtrar()
    {
        // Validar formato de fechas si alguna está presente
        if ($this->fechaDesdeFiltro || $this->fechaHastaFiltro) {

            // Validar formato correcto
            if (!strtotime($this->fechaDesdeFiltro) || !strtotime($this->fechaHastaFiltro)) {
                $this->dispatch('mostrarToastFechaReserva', [
                    'mensaje' => 'Una o ambas fechas no son válidas.'
                ]);
                return;
            }

            $fechaDesde = Carbon::parse($this->fechaDesdeFiltro);
            $fechaHasta = Carbon::parse($this->fechaHastaFiltro);
            $hoy = Carbon::today();

            // Validar que no sean menores al día actual
            if ($fechaDesde->lt($hoy) || $fechaHasta->lt($hoy)) {
                $this->dispatch('mostrarToastFechaReserva', [
                    'mensaje' => 'Las fechas no pueden ser anteriores al día de hoy.'
                ]);
                return;
            }

            // Validar rango correcto
            if ($fechaDesde->gt($fechaHasta)) {
                $this->dispatch('mostrarToastFechaReserva', [
                    'mensaje' => 'La fecha "desde" no puede ser mayor que la fecha "hasta".'
                ]);
                return;
            }
        }

        // Si pasa la validación, emitir evento con filtros
        $this->dispatch('filtrosActualizados', [
            'fecha_desde' => $this->fechaDesdeFiltro,
            'fecha_hasta' => $this->fechaHastaFiltro,
        ]);
    }



    public function resetFiltros()
    {
        $this->reset([
            'fechaDesdeFiltro',
            'fechaHastaFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'fecha_desde' => '',
            'fecha_hasta' => '',
        ]);


        $this->dispatch('limpiarFiltros');
    }

    public function render()
    {
        return view('livewire.reservations.filter-room-selector');
    }
}
