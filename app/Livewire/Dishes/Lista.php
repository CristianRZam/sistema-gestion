<?php

namespace App\Livewire\Dishes;

use App\Models\Dish;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    public $selectedDishes = [];        // IDs seleccionados
    public $selectAll = false;
    protected $paginationTheme = 'tailwind'; // Puedes usar 'bootstrap' si lo prefieres

    public $dishIdToDelete;

    // Filtros
    public $nombreFiltro = '';
    public $categoriaFiltro = []; // ← Ya lo estás haciendo bien

    public $estadoFiltro = '';

    protected $listeners = [
        'actualiza-lista-platillo' => '$refresh',
        'open-modal-delete-dish' => 'setDishIdToDelete',
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function updatedSelectAll($value)
    {
        $this->selectedDishes = [];
        if ($value) {
            // Query con tus filtros idénticos al render()
            $query = Dish::query()->whereNull('auditoriaFechaEliminacion');

            if ($this->nombreFiltro) {
                $query->where('nombre', 'like', '%'.$this->nombreFiltro.'%');
            }
            if (!empty($this->categoriaFiltro)) {
                $query->whereIn('categoria_id', $this->categoriaFiltro);
            }
            if ($this->estadoFiltro !== '') {
                $query->where('activo', (bool) $this->estadoFiltro);
            }


            // Pluck de **todos** los IDs filtrados
            $this->selectedDishes = $query->pluck('id')->toArray();
        }
    }

    public function actualizarFiltros($filtros)
    {
        $this->nombreFiltro = $filtros['nombre'] ?? '';
        $this->categoriaFiltro = $filtros['categoria_ids'] ?? '';
        $this->estadoFiltro = $filtros['estado'] ?? '';
        $this->selectedDishes = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function setDishIdToDelete($id = null)
    {
        $this->dishIdToDelete = $id;
    }

    public function delete()
    {
        if (!$this->dishIdToDelete) {
            session()->flash('error', 'Platillo no válido.');
            return;
        }

        $dish = Dish::find($this->dishIdToDelete);

        if (!$dish) {
            session()->flash('error', 'Platillo no encontrado.');
            return;
        }

        $dish->update([
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor' => Auth::id(),
        ]);

        $this->dispatch('actualiza-lista-platillo');
        $this->dispatch('cerrarModalDeteleDish');
        $this->reset('dishIdToDelete');
    }


    public function render()
    {

        $query = Dish::query()->whereNull('auditoriaFechaEliminacion');

        if ($this->nombreFiltro) {
            $query->where('nombre', 'like', '%' . $this->nombreFiltro . '%');
        }

        if (!empty($this->categoriaFiltro)) {
            $query->whereIn('categoria_id', $this->categoriaFiltro);
        }


        if ($this->estadoFiltro !== '') {
            $query->where('activo', (bool) $this->estadoFiltro);
        }

        $platillos = $query->paginate(10);


        return view('livewire.dishes.lista', [
            'platillos' => $platillos,
        ]);

    }

}
