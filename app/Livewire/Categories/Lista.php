<?php

namespace App\Livewire\Categories;

use App\Models\Parameter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Lista extends Component
{
    public $categorias;
    public $categoryIdToDelete;

    protected $listeners = [
        'actualiza-lista-categoria' => 'actualizarCategorias',
        'open-modal-delete-category' => 'setCategoryIdToDelete',
        ];

    public function actualizarCategorias()
    {
        $this->categorias = Parameter::where('tipo', 'CATEGORIA')
            ->whereNull('auditoriaFechaEliminacion')
            ->orderBy('orden')
            ->get();

    }

    public function render()
    {
        $this->actualizarCategorias();

        return view('livewire.categories.lista', [
            'categorias' => $this->categorias,
        ]);
    }


    public function setCategoryIdToDelete($id = null)
    {
        $this->categoryIdToDelete = $id;
    }

    public function deleteCategory()
    {
        if (!$this->categoryIdToDelete) {
            session()->flash('error', 'Categoría no válida.');
            return;
        }

        $parameter = Parameter::find($this->categoryIdToDelete);

        if (!$parameter) {
            session()->flash('error', 'Categoría no encontrada.');
            return;
        }

        $parameter->update([
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor' => Auth::id(),
        ]);

        //session()->flash('success', 'Categoría eliminada correctamente.');
        $this->dispatch('actualiza-lista-categoria');
        $this->dispatch('cerrarModalDeteleCategory');
        $this->reset('categoryIdToDelete');
    }
}
