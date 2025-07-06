<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class Filter extends Component
{
    public $nombreFiltro = '';
    public $rolFiltro = [];


    public $roles= [];

    protected $listeners = ['actualizarRolesDesdeJS' => 'actualizarRoles'];

    public function actualizarRoles($valores = [])
    {
        $this->rolFiltro = $valores;
    }


    public function mount()
    {
        $this->roles = Role::orderBy('name')->get();
    }

    public function filtrar()
    {
        $this->dispatch('filtrosActualizados', [
            'nombre' => $this->nombreFiltro,
            'rol_ids' => $this->rolFiltro,
        ]);
    }

    public function resetFiltros()
    {
        $this->reset([
            'nombreFiltro',
            'rolFiltro',
        ]);

        $this->dispatch('filtrosActualizados', [
            'nombre' => '',
            'rol_ids' => [],
        ]);

        $this->dispatch('limpiarFiltros');
    }


    public function render()
    {
        return view('livewire.users.filter');
    }
}
