<?php

namespace App\Livewire\Sales;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Sale;
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

        $this->resetPage(); // Reinicia la paginación al aplicar nuevos filtros
    }
    public function render()
    {
        $query = Sale::with(['vendedor', 'customer', 'estadoVenta']);

        // Validar y aplicar fecha desde y hasta si ambas están presentes y válidas
        if ($this->fechaDesdeFiltro && $this->fechaHastaFiltro) {
            $query->whereBetween('fecha_venta', [$this->fechaDesdeFiltro, $this->fechaHastaFiltro]);
        }

        // Validar y aplicar filtro de estados si es un array no vacío
        if (is_array($this->estadoFiltro) && !empty($this->estadoFiltro)) {
            $query->whereIn('estado_venta_id', $this->estadoFiltro);
        }

        // Validar y aplicar filtro de usuarios si es un array no vacío
        if (is_array($this->usuarioFiltro) && !empty($this->usuarioFiltro)) {
            $query->whereIn('usuario_id', $this->usuarioFiltro);
        }

        // Ordenamiento por prioridad de fechas
        $query->orderByRaw('fecha_venta IS NULL DESC')
            ->orderBy('fecha_venta', 'desc');

        // Paginación final
        $ventas = $query->paginate(10);

        return view('livewire.sales.lista', [
            'ventas' => $ventas
        ]);
    }


}
