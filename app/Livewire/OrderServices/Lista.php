<?php

namespace App\Livewire\OrderServices;

use App\Models\OrderService;
use Carbon\Carbon;
use Livewire\Component;

class Lista extends Component
{
    public $fechaDesdeFiltro = '';
    public $fechaHastaFiltro = '';
    public $estadoFiltro = [];

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

        $this->resetPage(); // Reinicia la paginación al aplicar nuevos filtros
    }

    public function render()
    {
        $query = OrderService::with(['encargado', 'customer', 'estado']);

        // Validar y aplicar fecha desde y hasta si ambas están presentes y válidas
        if ($this->fechaDesdeFiltro && $this->fechaHastaFiltro) {
            $query->whereBetween('fecha', [$this->fechaDesdeFiltro, $this->fechaHastaFiltro]);
        }

        // Validar y aplicar filtro de estados si es un array no vacío
        if (is_array($this->estadoFiltro) && !empty($this->estadoFiltro)) {
            $query->whereIn('estado_id', $this->estadoFiltro);
        }


        // Ordenamiento por prioridad de fechas
        $query->orderByRaw('fecha IS NULL DESC')
            ->orderBy('fecha', 'desc');

        // Paginación final
        $servicios = $query->paginate(10);
        return view('livewire.order-services.lista',[
        'servicios' => $servicios
        ]);
    }
}
