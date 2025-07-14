<?php

namespace App\Livewire\Reservations;

use App\Models\Customer;
use App\Models\ReservationRoom;
use Livewire\Component;

class Register extends Component
{
    public $cliente_nombre = '';
    public $cliente_seleccionado = null;

    public $reservationId;
    public $detallesHabitaciones = [];

    public function mount($id)
    {
        $this->reservationId = $id;

        $this->detallesHabitaciones = ReservationRoom::with('room')
            ->where('reservation_id', $this->reservationId)
            ->get()
            ->toArray();
    }

    protected $listeners = [
        'clienteSeleccionadoDesdeVenta' => 'cargarClienteDesdeModal',
    ];

    public function cargarClienteDesdeModal($clienteId)
    {
        $cliente = Customer::find($clienteId);

        if ($cliente) {
            $this->cliente_seleccionado = [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'dni' => $cliente->documento,
                'direccion' => $cliente->direccion,
            ];

            // También puedes llenar otros campos si necesitas
            $this->cliente_nombre = $cliente->nombre;
        }
    }

    public function eliminarClienteSeleccionado()
    {
        $this->cliente_seleccionado = null;
        $this->cliente_nombre = null; // por si estás usando este campo también
    }

    public function render()
    {
        return view('livewire.reservations.register');
    }
}
