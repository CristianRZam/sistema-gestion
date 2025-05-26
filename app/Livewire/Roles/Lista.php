<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class Lista extends Component
{
    public $roles;

    protected $listeners = [
        'actualiza-lista-role' => 'actualizarRoles',
    ];

    public function mount()
    {
        $this->actualizarRoles(); // Cargar los roles al iniciar
    }

    public function actualizarRoles()
    {
        $this->roles = Role::orderBy('name')->get();
    }


    public function render()
    {
        return view('livewire.roles.lista', [
            'roles' => $this->roles,
        ]);
    }
}
