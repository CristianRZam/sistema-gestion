<?php

namespace App\Livewire\Reservations;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Parameter;
use Carbon\Carbon;
use Livewire\Component;

class RoomSelector extends Component
{

    public $fechaDesdeFiltro = '';
    public $fechaHastaFiltro = '';
    public $pisoActivo;

    protected $listeners = [
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function actualizarFiltros($filtros)
    {
        $this->fechaDesdeFiltro = $filtros['fecha_desde']
            ? Carbon::parse($filtros['fecha_desde'])->startOfDay()->toDateTimeString()
            : '';

        $this->fechaHastaFiltro = $filtros['fecha_hasta']
            ? Carbon::parse($filtros['fecha_hasta'])->endOfDay()->toDateTimeString()
            : '';
    }


    public function mount()
    {
        $primerPiso = Parameter::where('codigoParametro', 'PISO_HABITACION')->orderBy('orden')->first();
        $this->pisoActivo = $primerPiso?->idParametro;
    }

    public function setPisoActivo($id)
    {
        $this->pisoActivo = $id;
    }
    public function render()
    {
        $pisos = Parameter::where('codigoParametro', 'PISO_HABITACION')->orderBy('orden')->get();
        $habitaciones = collect();

        if ($this->fechaDesdeFiltro && $this->fechaHastaFiltro) {
            $desde = Carbon::parse($this->fechaDesdeFiltro)->startOfDay();
            $hasta = Carbon::parse($this->fechaHastaFiltro)->endOfDay();

            $habitaciones = Room::with(['tipo', 'estado'])
                ->where('piso_id', $this->pisoActivo)
                ->orderBy('numero')
                ->get()
                ->map(function ($habitacion) use ($desde, $hasta) {

                    $reserva = Reservation::whereNull('auditoriaFechaEliminacion')
                        ->whereHas('rooms', fn($q) => $q->where('room_id', $habitacion->id))
                        ->where(function ($query) use ($desde, $hasta) {
                            $query
                                ->whereBetween('fecha_inicio', [$desde, $hasta])
                                ->orWhereBetween('fecha_fin', [$desde, $hasta])
                                ->orWhere(function ($q) use ($desde, $hasta) {
                                    $q->where('fecha_inicio', '<=', $desde)
                                        ->where('fecha_fin', '>=', $hasta);
                                });
                        })
                        ->orderBy('fecha_inicio')
                        ->first();

                    // Si hay una reserva activa para esta habitación en ese rango
                    if ($habitacion->estado_id == 3) {
                        // Inhabilitada siempre tiene prioridad
                        $habitacion->estado_nombre = 'Inhabilitada';
                    } elseif ($reserva) {
                        $habitacion->estado_id = $reserva->estado_id;
                        $habitacion->estado_nombre = Parameter::find($reserva->estado_id)?->nombre ?? 'Desconocido';
                    } elseif ($habitacion->estado_id == 6) {
                        $habitacion->estado_nombre = 'Ocupada';
                    } else {
                        $habitacion->estado_id = 1;
                        $habitacion->estado_nombre = 'Disponible';
                    }



                    return $habitacion;
                });
        }

        return view('livewire.reservations.room-selector', [
            'pisos' => $pisos,
            'habitaciones' => $habitaciones,
        ]);
    }

}
