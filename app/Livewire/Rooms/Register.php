<?php

namespace App\Livewire\Rooms;

use App\Models\Parameter;
use App\Models\Room;
use Carbon\Carbon;
use Livewire\Component;

class Register extends Component
{
    public $roomId = null;
    public $numero;
    public $tipo;
    public $piso;
    public $capacidad;
    public $precio;
    public $precioPromocion;
    public $descripcion;
    public $tipos = [];
    public $pisos = [];

    protected $rules = [
        'numero' => 'required|string|max:255',
        'tipo' => 'required|numeric|min:1',
        'piso' => 'required|numeric|min:1',
        'capacidad' => 'required|integer|min:1',
        'precio' => 'required|numeric|min:0',
        'precioPromocion'   => 'nullable|numeric|min:0',
        'descripcion' => 'nullable|string',
    ];

    protected $listeners = ['open-modal-room' => 'abrir',
        'setDescripcionSummernote'];

    public function setDescripcionSummernote($data)
    {
        $this->descripcion = $data;
    }

    public function mount()
    {
        $this->tipos = Parameter::where('codigoParametro', 'TIPO_HABITACION')->orderBy('orden')->pluck('nombre', 'idParametro')->toArray();
        $this->pisos = Parameter::where('codigoParametro', 'PISO_HABITACION')->orderBy('orden')->pluck('nombre', 'idParametro')->toArray();
    }

    public function abrir($id = null)
    {
        $this->resetValidation();
        $this->roomId = $id;

        if ($this->roomId) {
            $habitacion = Room::find($this->roomId);
            if ($habitacion) {
                $this->numero = $habitacion->numero;
                $this->tipo = $habitacion->tipo_id;
                $this->piso = $habitacion->piso_id;
                $this->capacidad = $habitacion->capacidad;
                $this->precio = $habitacion->precio;
                $this->precioPromocion = $habitacion->precio_promocion;
                $this->descripcion = $habitacion->descripcion;
            }
        } else {
            $this->reset(['numero', 'tipo', 'piso', 'capacidad', 'precio', 'precioPromocion', 'descripcion']);
        }

        $this->dispatch('inicializarDescripcion', [
            'descripcion' => $this->descripcion,
        ]);

        $this->dispatch('abrirModalRoom');
    }

    public function guardar()
    {
        $this->validate([
            ...($this->roomId
                ? array_merge($this->rules, [
                    'numero' => 'required|string|max:50|unique:rooms,numero,' . $this->roomId,
                ])
                : array_merge($this->rules, [
                    'numero' => 'required|string|max:50|unique:rooms,numero',
                ])),
        ]);

        $userId = auth()->id();

        if ($this->roomId) {
            $room = Room::find($this->roomId);
            if ($room) {
                $room->update([
                    'numero' => $this->numero,
                    'tipo_id' => $this->tipo,
                    'piso_id' => $this->piso,
                    'capacidad' => $this->capacidad,
                    'precio' => $this->precio,
                    'precio_promocion' => $this->precioPromocion !== ''
                        ? $this->precioPromocion
                        : null,
                    'descripcion' => $this->descripcion,
                    'auditoriaFechaModificacion' => Carbon::now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            }
        } else {
            $room = Room::create([
                'numero' => $this->numero,
                'tipo_id' => $this->tipo,
                'piso_id' => $this->piso,
                'capacidad' => $this->capacidad,
                'precio' => $this->precio,
                'precio_promocion' => $this->precioPromocion !== ''
                    ? $this->precioPromocion
                    : null,
                'descripcion' => $this->descripcion,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }


        $this->dispatch('actualiza-lista-habitacion');
        $this->dispatch('cerrarModalRoom');
    }

    public function render()
    {
        return view('livewire.rooms.register');
    }
}
