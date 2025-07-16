<?php

namespace App\Livewire\Reservations;

use App\Models\Checkin;
use App\Models\Checkout;
use App\Models\ReservationRoom;
use Carbon\Carbon;
use Livewire\Component;

class RegisterCheck extends Component
{
    public $checkId = null;
    public $tipoCheck;
    public $fecha;

    public $reservationRoomId;
    public $yaRegistrado = false;
    public $fechaInicioReserva;
    public $fechaFinReserva;

    protected $listeners = ['open-modal-check' => 'abrir'];

    public function abrir($id = null, $tipo = null)
    {
        $this->resetValidation();

        $this->checkId = null;
        $this->tipoCheck = $tipo;
        $this->reservationRoomId = $id;
        $this->yaRegistrado = false;

        $detalle = ReservationRoom::find($id);

        if (!$detalle) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => 'No se encontró el detalle de habitación.']);
            return;
        }

        $this->fechaInicioReserva = Carbon::parse($detalle->fecha_inicio)->format('Y-m-d');
        $this->fechaFinReserva = Carbon::parse($detalle->fecha_fin)->format('Y-m-d');

        if ($tipo === 'check-in') {
            $checkIn = Checkin::where('reservation_room_id', $this->reservationRoomId)->whereNull('auditoriaFechaEliminacion')->first();

            if ($checkIn) {
                $this->fecha = Carbon::parse($checkIn->fecha_checkin)->format('Y-m-d\TH:i');
                $this->yaRegistrado = true;
                $this->checkId = $checkIn->id;
            } else {
                $this->fecha = now()->format("Y-m-d\TH:i");
            }
        } elseif ($tipo === 'check-out') {
            $checkOut = Checkout::where('reservation_room_id', $detalle->id)->whereNull('auditoriaFechaEliminacion')->first();

            if ($checkOut) {
                $this->fecha = Carbon::parse($checkOut->fecha_checkout)->format('Y-m-d\TH:i');
                $this->yaRegistrado = true;
                $this->checkId = $checkOut->id;
            } else {
                $this->fecha = now()->format("Y-m-d\TH:i");
            }
        }

        $this->dispatch('abrirModalCheck');
    }

    public function guardar()
    {
        if ($this->yaRegistrado) return;

        $this->validate([
            'fecha' => 'required|date',
        ]);

        $fecha = Carbon::parse($this->fecha);
        $userId = auth()->id();
        $fechaInicioReserva = Carbon::parse($this->fechaInicioReserva)->startOfDay();
        $fechaFinReserva = Carbon::parse($this->fechaFinReserva)->endOfDay();

        // Validación común: no puede ser antes del inicio de la reserva
        if ($fecha->lt($fechaInicioReserva)) {
            $this->addError('fecha', 'La fecha no puede ser anterior a la fecha de inicio de la reserva.');
            return;
        }

        if ($this->tipoCheck === 'check-in') {
            if ($fecha->gt($fechaFinReserva)) {
                $this->addError('fecha', 'La fecha de check-in no puede ser mayor a la fecha fin de la reserva.');
                return;
            }

            Checkin::create([
                'reservation_room_id' => $this->reservationRoomId,
                'fecha_checkin' => $fecha,
                'user_id' => $userId,
                'auditoriaFechaCreacion' => now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        if ($this->tipoCheck === 'check-out') {
            if ($fecha->gt(now())) {
                $this->addError('fecha', 'La fecha de check-out no puede ser en el futuro.');
                return;
            }

            $checkIn = Checkin::where('reservation_room_id', $this->reservationRoomId)->whereNull('auditoriaFechaEliminacion')->first();
            if ($checkIn && $fecha->lt(Carbon::parse($checkIn->fecha_checkin))) {
                $this->addError('fecha', 'La fecha de check-out no puede ser anterior al check-in.');
                return;
            }

            Checkout::create([
                'reservation_room_id' => $this->reservationRoomId,
                'fecha_checkout' => $fecha->toDateTimeString(),
                'user_id' => $userId,
                'auditoriaFechaCreacion' => now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        $this->dispatch('cerrarModalCheck');
    }



    public function render()
    {
        return view('livewire.reservations.register-check');
    }
}
