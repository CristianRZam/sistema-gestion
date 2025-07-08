<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
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

        $this->resetPage(); // Reinicia la paginación al aplicar nuevos filtros
    }

    public function render()
    {
        $query = Purchase::with(['comprador', 'supplier', 'estadoCompra']);

        // Filtro por rango de fechas
        if ($this->fechaDesdeFiltro && $this->fechaHastaFiltro) {
            $query->whereBetween('fecha_compra', [$this->fechaDesdeFiltro, $this->fechaHastaFiltro]);
        }

        // Filtro por estados
        if (is_array($this->estadoFiltro) && !empty($this->estadoFiltro)) {
            $query->whereIn('estado_compra_id', $this->estadoFiltro);
        }

        // Filtro por usuarios (comprador)
        if (is_array($this->usuarioFiltro) && !empty($this->usuarioFiltro)) {
            $query->whereIn('usuario_id', $this->usuarioFiltro);
        }

        $query->orderByRaw('fecha_compra IS NULL DESC')
            ->orderBy('fecha_compra', 'desc');

        $compras = $query->paginate(10);

        return view('livewire.purchases.lista', [
            'compras' => $compras,
        ]);
    }

}
