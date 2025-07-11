<?php

namespace App\Livewire\Reservations;

use App\Models\Reservation;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    public $fechaDesdeFiltro = '';
    public $fechaHastaFiltro = '';
    public $estadoFiltro = [];
    public $usuarioFiltro = [];
    protected $paginationTheme = 'tailwind';

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

        $this->estadoFiltro = $filtros['estado_ids'] ?? [];
        $this->usuarioFiltro = $filtros['usuario_ids'] ?? [];

        $this->resetPage();
    }
    public function render()
    {
        $query = Reservation::with(['estado']);

        // Validar y aplicar fecha desde y hasta si ambas están presentes y válidas
        if ($this->fechaDesdeFiltro && $this->fechaHastaFiltro) {
            $query->whereBetween('fecha_reserva', [$this->fechaDesdeFiltro, $this->fechaHastaFiltro]);
        }

        // Validar y aplicar filtro de estados si es un array no vacío
        if (is_array($this->estadoFiltro) && !empty($this->estadoFiltro)) {
            $query->whereIn('estado_id', $this->estadoFiltro);
        }

        // Validar y aplicar filtro de usuarios si es un array no vacío
        if (is_array($this->usuarioFiltro) && !empty($this->usuarioFiltro)) {
            $query->whereIn('user_id', $this->usuarioFiltro);
        }

        // Ordenamiento por prioridad de fechas
        $query->orderByRaw('fecha_reserva IS NULL DESC')
            ->orderBy('fecha_reserva', 'desc');

        // Paginación final
        $reservas = $query->paginate(10);


        return view('livewire.reservations.lista', [
        'reservas' => $reservas
        ]);
    }
}
