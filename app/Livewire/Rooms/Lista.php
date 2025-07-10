<?php

namespace App\Livewire\Rooms;

use App\Models\Room;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    public $habitacionIdModal;
    public $estadoHabitacionModal;

    protected $paginationTheme = 'tailwind'; // O bootstrap si usas Bootstrap

    // Filtros
    public $tipoFiltro = [];
    public $pisoFiltro = [];
    public $estadoFiltro = [];

    protected $listeners = [
        'actualiza-lista-habitacion' => '$refresh',
        'open-modal-room-disable' => 'abrirModalCambioEstado',
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function actualizarFiltros($filtros)
    {
        $this->tipoFiltro = $filtros['tipo_ids'] ?? '';
        $this->pisoFiltro = $filtros['piso_ids'] ?? '';
        $this->estadoFiltro = $filtros['estado_ids'] ?? '';
        $this->resetPage();
    }
    public function abrirModalCambioEstado($id)
    {
        $room = Room::find($id);

        if (!$room) {
            session()->flash('error', 'Habitación no encontrado.');
            return;
        }

        $this->habitacionIdModal = $room->id;
        $this->estadoHabitacionModal = $room->estado_id;
    }


    public function confirmarCambioEstado()
    {
        $room = Room::find($this->habitacionIdModal);

        if (!$room) {
            session()->flash('error', 'Habitación no encontrada.');
            return;
        }

        $room->estado_id = ($room->estado_id == 3) ? 1 : 3;
        $room->save();

        $this->dispatch('actualiza-lista-habitacion');
        $this->reset(['habitacionIdModal', 'estadoHabitacionModal']);
        $this->dispatch('cerrarModalRoomDisable');
    }


    public function render()
    {
        $query = Room::query()->with(['tipo', 'piso', 'estado']);

        if (!empty($this->tipoFiltro)) {
            $query->whereIn('tipo_id', $this->tipoFiltro);
        }

        if (!empty($this->pisoFiltro)) {
            $query->whereIn('piso_id', $this->pisoFiltro);
        }

        if (!empty($this->estadoFiltro)) {
            $query->whereIn('estado_id', $this->estadoFiltro);
        }

        return view('livewire.rooms.lista', [
            'habitaciones' => $query->paginate(10),
        ]);
    }
}
