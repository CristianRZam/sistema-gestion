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

    // Filtros
    public $nombreFiltro = '';
    public $tipoFiltro = [];
    public $codigoFiltro = '';

    protected $listeners = [
        'actualiza-lista-parametro' => '$refresh',
        'open-modal-delete-parameter' => 'setParameterIdToDelete',
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function actualizarFiltros($filtros)
    {
        $this->nombreFiltro = $filtros['nombre'] ?? '';
        $this->tipoFiltro = $filtros['tipo_ids'] ?? '';
        $this->codigoFiltro = $filtros['codigo'] ?? '';
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
        $query = Parameter::query()->whereNull('auditoriaFechaEliminacion');

        if ($this->nombreFiltro) {
            $query->where('nombre', 'like', '%' . $this->nombreFiltro . '%');
        }

        if (!empty($this->tipoFiltro)) {
            $query->whereIn('tipo', $this->tipoFiltro);
        }


        if ($this->codigoFiltro) {
            $query->where('codigoParametro', 'like', '%' . $this->codigoFiltro . '%');
        }

        $parametros = $query->paginate(10);

        return view('livewire.parameters.lista', [
            'parametros' => $parametros,
        ]);
    }
}
