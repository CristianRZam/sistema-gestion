<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class Register extends Component
{
    public $rolId = null;
    public $nombre;

    protected $rules = [
        'nombre' => 'required|string|max:255',
    ];

    protected $listeners = ['open-modal-role' => 'abrir'];

    public function abrir($id = null)
    {
        $this->resetValidation();

        $this->rolId = $id;

        if ($this->rolId) {
            $rol = Role::find($this->rolId);
            if ($rol) {
                $this->nombre = $rol->name;
            }
        } else {
            $this->reset(['nombre']);
        }
    }

    public function guardarRole()
    {
        $this->validate();

        $userId = auth()->id();

        if ($this->rolId) {
            // Modo edición
            $rol = Role::find($this->rolId);
            if ($rol) {
                $rol->update([
                    'name' => $this->nombre,
                ]);
            }
        } else {
            // Modo creación
            Role::create([
                'name' => $this->nombre,
                'guard_name' => 'web', // Puedes cambiar esto si usas otro guard
            ]);
        }

        $this->dispatch('actualiza-lista-role');
        $this->dispatch('cerrarModalRole');
    }

    public function render()
    {
        return view('livewire.roles.register');
    }
}
