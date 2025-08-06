<?php

namespace App\Livewire\Tables;

use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    public $selectedTables = [];        // IDs seleccionados
    public $selectAll = false;
    protected $paginationTheme = 'tailwind'; // Puedes usar 'bootstrap' si lo prefieres

    public $tableIdToDelete;

    // Filtros
    public $codigoFiltro = '';

    public $estadoFiltro = '';

    protected $listeners = [
        'actualiza-lista-mesa' => '$refresh',
        'open-modal-delete-table' => 'setTableIdToDelete',
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function updatedSelectAll($value)
    {
        $this->selectedTables = [];
        if ($value) {
            // Query con tus filtros idénticos al render()
            $query = RestaurantTable::query()->whereNull('auditoriaFechaEliminacion');

            if ($this->codigoFiltro) {
                $query->where('codigo', 'like', '%'.$this->codigoFiltro.'%');
            }
            if ($this->estadoFiltro !== '') {
                $query->where('activa', (bool) $this->estadoFiltro);
            }


            // Pluck de **todos** los IDs filtrados
            $this->selectedTables = $query->pluck('id')->toArray();
        }
    }

    public function actualizarFiltros($filtros)
    {
        $this->codigoFiltro = $filtros['codigo'] ?? '';
        $this->estadoFiltro = $filtros['estado'] ?? '';
        $this->selectedTables = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function setTableIdToDelete($id = null)
    {
        $this->tableIdToDelete = $id;
    }

    public function delete()
    {
        if (!$this->tableIdToDelete) {
            session()->flash('error', 'Mesa no válida.');
            return;
        }

        $dish = RestaurantTable::find($this->tableIdToDelete);

        if (!$dish) {
            session()->flash('error', 'Mesa no encontrada.');
            return;
        }

        $dish->update([
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor' => Auth::id(),
        ]);

        $this->dispatch('actualiza-lista-mesa');
        $this->dispatch('cerrarModalDeleteTable');
        $this->reset('tableIdToDelete');
    }


    public function render()
    {
        $query = RestaurantTable::query()->whereNull('auditoriaFechaEliminacion');

        if ($this->codigoFiltro) {
            $query->where('codigo', 'like', '%' . $this->codigoFiltro . '%');
        }

        if ($this->estadoFiltro !== '') {
            $query->where('activa', (bool) $this->estadoFiltro);
        }

        $mesas = $query->paginate(10);

        return view('livewire.tables.lista', [
            'mesas' => $mesas,
        ]);
    }
}
