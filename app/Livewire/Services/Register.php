<?php

namespace App\Livewire\Services;


use App\Models\Service;
use Carbon\Carbon;
use Livewire\Component;

class Register extends Component
{
    public $serviceId = null;
    public $nombre;
    public $precio;
    public $descripcion;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'precio' => 'required|numeric|min:0',
        'descripcion' => 'nullable|string',
    ];

    protected $listeners = ['open-modal-service' => 'abrir',
        'setDescripcionSummernote'];

    public function setDescripcionSummernote($data)
    {
        $this->descripcion = $data;
    }

    public function abrir($id = null)
    {
        $this->resetValidation();
        $this->serviceId = $id;

        if ($this->serviceId) {
            $servicio = Service::find($this->serviceId);
            if ($servicio) {
                $this->nombre = $servicio->nombre;
                $this->precio = $servicio->precio;
                $this->descripcion = $servicio->descripcion;
            }
        } else {
            $this->reset(['nombre', 'precio', 'descripcion']);
        }

        $this->dispatch('inicializarDescripcion', [
            'descripcion' => $this->descripcion,
        ]);

        $this->dispatch('abrirModalService');
    }

    public function guardar()
    {
        $this->validate([
            ...($this->serviceId
                ? array_merge($this->rules, [
                    'nombre' => 'required|string|max:255|unique:services,nombre,' . $this->serviceId,
                ])
                : array_merge($this->rules, [
                    'nombre' => 'required|string|max:255|unique:services,nombre',
                ])),
        ]);

        $userId = auth()->id();

        if ($this->serviceId) {
            $service = Service::find($this->serviceId);
            if ($service) {
                $service->update([
                    'nombre' => $this->nombre,
                    'precio' => $this->precio,
                    'descripcion' => $this->descripcion,
                    'auditoriaFechaModificacion' => Carbon::now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            }
        } else {
            $service = Service::create([
                'nombre' => $this->nombre,
                'precio' => $this->precio,
                'descripcion' => $this->descripcion,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }


        $this->dispatch('actualiza-lista-servicio');
        $this->dispatch('cerrarModalService');
    }
    public function render()
    {
        return view('livewire.services.register');
    }
}
