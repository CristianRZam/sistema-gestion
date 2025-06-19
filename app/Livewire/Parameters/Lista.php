<?php

namespace App\Livewire\Parameters;

use App\Models\Parameter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    public $parameterIdToDelete;

    protected $paginationTheme = 'tailwind'; // Usa Tailwind si estás usando Tailwind CSS

    protected $listeners = [
        'actualiza-lista-parametro' => '$refresh',
        'open-modal-delete-parameter' => 'setParameterIdToDelete',
    ];

    public function updatingSearch() // Si luego deseas agregar búsqueda
    {
        $this->resetPage();
    }

    public function setParameterIdToDelete($id = null)
    {
        $this->parameterIdToDelete = $id;
    }

    public function deleteParameter()
    {
        if (!$this->parameterIdToDelete) {
            session()->flash('error', 'Parametro no válido.');
            return;
        }

        $parameter = Parameter::find($this->parameterIdToDelete);

        if (!$parameter) {
            session()->flash('error', 'Parametro no encontrado.');
            return;
        }

        $parameter->update([
            'auditoriaFechaEliminacion' => now(),
            'auditoriaEliminadoPor' => auth()->id(),
        ]);

        $this->dispatch('cerrarModalDeteleParameter');
        $this->reset('parameterIdToDelete');
    }

    public function render()
    {
        $parametros = Parameter::whereNull('auditoriaFechaEliminacion')
            ->orderBy('codigoParametro')
            ->orderBy('orden')
            ->paginate(10); // <- Cambia aquí el número de resultados por página

        return view('livewire.parameters.lista', [
            'parametros' => $parametros,
        ]);
    }
}
