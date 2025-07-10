<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    public $servicioIdModal;
    public $estadoServicioModal;

    protected $paginationTheme = 'tailwind'; // O bootstrap si usas Bootstrap


    protected $listeners = [
        'actualiza-lista-servicio' => '$refresh',
        'open-modal-service-disable' => 'abrirModalCambioEstado',
    ];

    public function abrirModalCambioEstado($id)
    {
        $service = Service::find($id);

        if (!$service) {
            session()->flash('error', 'Servicio no encontrado.');
            return;
        }

        $this->servicioIdModal = $service->id;
        $this->estadoServicioModal = $service->activo;
    }


    public function confirmarCambioEstado()
    {
        $service = Service::find($this->servicioIdModal);

        if (!$service) {
            session()->flash('error', 'Servicio no encontrada.');
            return;
        }

        $service ->activo = !$service->activo;
        $service ->save();

        $this->dispatch('actualiza-lista-servicio');
        $this->reset(['servicioIdModal', 'estadoServicioModal']);
        $this->dispatch('cerrarModalServicioDisable');
    }

    public function render()
    {
        $servicios = Service::query()->paginate(10);

        return view('livewire.services.lista', [
            'servicios' => $servicios,
        ]);
    }

}
