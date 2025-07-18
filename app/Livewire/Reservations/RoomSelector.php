<?php

namespace App\Livewire\Reservations;

use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\Parameter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RoomSelector extends Component
{

    public $fechaDesdeFiltro = '';
    public $fechaHastaFiltro = '';
    public $pisoActivo;
    public $habitacionesSeleccionadas = [];


    public function toggleSeleccion($habitacionId)
    {
        if (in_array($habitacionId, $this->habitacionesSeleccionadas)) {
            $this->habitacionesSeleccionadas = array_diff($this->habitacionesSeleccionadas, [$habitacionId]);
        } else {
            $this->habitacionesSeleccionadas[] = $habitacionId;
        }
    }

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

    public function continuarReserva()
    {
        if (empty($this->habitacionesSeleccionadas)) {
            $this->dispatch('errorSelectorReservation', ['mensaje' => "No hay habitaciones seleccionadas."]);
            return;
        }

        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $now = Carbon::now();

            // 1. Crear la reserva
            $reserva = Reservation::create([
                'estado_id' => 1, // Primer estado: borrador
                'user_id' => $userId,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
            ]);

            // 2. Fechas
            $fechaInicio = Carbon::parse($this->fechaDesdeFiltro)->startOfDay();
            $fechaFin = Carbon::parse($this->fechaHastaFiltro)->startOfDay();

            // Calcular días (mínimo 1)
            $dias = $fechaInicio->diffInDays($fechaFin);
            if ($dias < 1) $dias = 1;

            // 3. Crear cada detalle de habitación
            foreach ($this->habitacionesSeleccionadas as $roomId) {
                $habitacion = Room::findOrFail($roomId);

                $precio = $habitacion->precio_promocion ?? $habitacion->precio;
                $subtotal = $precio * $dias;

                ReservationRoom::create([
                    'reservation_id' => $reserva->id,
                    'room_id' => $habitacion->id,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'cantidad_personas' => 1,
                    'precio' => $precio,
                    'subtotal' => $subtotal,
                    'auditoriaFechaCreacion' => $now,
                    'auditoriaCreadoPor' => $userId,
                ]);
            }

            DB::commit();

            return redirect()->route('reservations.register', ['id' => $reserva->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('errorSelectorReservation', ['mensaje' => 'Error al crear la reserva: ' . $e->getMessage()]);
        }
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

                    // Buscar si hay una reserva activa en reservation_rooms
                    $reservaRoom = ReservationRoom::whereHas('reservation', function ($q) {
                        $q->whereNull('auditoriaFechaEliminacion');
                    })
                        ->where('room_id', $habitacion->id)
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

                    // Evaluar estado de la habitación según su estado_id y la reserva encontrada
                    if ($habitacion->estado_id == 3) {
                        $habitacion->estado_nombre = 'Inhabilitada';
                    } elseif ($reservaRoom) {
                        $habitacion->estado_id = $reservaRoom->reservation->estado_id;
                        $habitacion->estado_nombre = Parameter::find($reservaRoom->reservation->estado_id)?->nombre ?? 'Desconocido';
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
