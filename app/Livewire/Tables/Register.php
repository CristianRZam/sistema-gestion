<?php

namespace App\Livewire\Tables;

use App\Models\Parameter;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileNotPreviewableException;
use Livewire\WithFileUploads;

class Register extends Component
{
    public $mesaId = null;
    public $codigo;
    public $nombre;
    public $piso;
    public $capacidad;
    public $estado;
    public $pisosDisponibles = [];


    protected $rules = [
        'codigo' => 'required|string|max:50',
        'nombre' => 'required|string|max:255',
        'piso' => 'nullable|integer|min:1',
        'capacidad' => 'required|integer|min:1',
        'estado'   => 'nullable|integer|min:1',
    ];

    protected $listeners = ['open-modal-table' => 'abrir',];

    public function mount()
    {
        $this->pisosDisponibles = $this->obtenerPisos();
    }

    public function abrir($id = null)
    {
        $this->resetValidation();
        $this->mesaId = $id;

        $this->pisosDisponibles = $this->obtenerPisos();

        if ($this->mesaId) {
            $mesa = RestaurantTable::find($this->mesaId);
            if ($mesa){
                $this->codigo = $mesa->codigo;
                $this->nombre = $mesa->nombre;
                $this->piso = $mesa->piso_id;
                $this->capacidad = $mesa->capacidad;
                $this->estado = $mesa->activa ? '1' : '2';

            }
        } else {
            $this->reset(['codigo', 'nombre', 'piso', 'capacidad', 'estado']);
        }

        $this->dispatch('abrirModalTable');
    }


    public function guardar()
    {
        $this->validate([
            ...($this->mesaId
                ? array_merge($this->rules, [
                    'codigo' => 'required|string|max:50|unique:restaurant_tables,codigo,' . $this->mesaId,
                ])
                : array_merge($this->rules, [
                    'codigo' => 'required|string|max:50|unique:restaurant_tables,codigo',
                ])),
        ]);

        $userId = auth()->id();

        if ($this->mesaId) {
            $mesa= RestaurantTable::find($this->mesaId);
            if ($mesa) {
                $mesa->update([
                    'codigo' => $this->codigo,
                    'nombre' => $this->nombre,
                    'piso_id' => $this->piso,
                    'capacidad' => $this->capacidad,
                    'activa' => $this->estado === '1',
                    'auditoriaFechaModificacion' => Carbon::now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            }
        } else {
            $mesa = RestaurantTable::create([
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
                'piso_id' => $this->piso,
                'capacidad' => $this->capacidad,
                'activa' => $this->estado === '1',
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        $this->dispatch('actualiza-lista-mesa');
        $this->dispatch('cerrarModalTable');
    }

    public function obtenerPisos()
    {
        return Parameter::where('codigoParametro', 'PISO_RESTAURANTE')->orderBy('nombre')->pluck('nombre', 'idParametro')->toArray();
    }

    public function render()
    {
        return view('livewire.tables.register');
    }
}
