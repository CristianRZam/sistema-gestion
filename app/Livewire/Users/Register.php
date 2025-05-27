<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Register extends Component
{
    public $userId = null;
    public $nombre;
    public $email;
    public $role;
    public $rolesDisponibles = [];

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'email' => 'required|email',
        'role' => 'required|string|exists:roles,name',
    ];

    protected $listeners = ['open-modal-user' => 'abrir'];

    public function mount()
    {
        $this->rolesDisponibles = Role::orderBy('name')->pluck('name')->toArray();
    }

    public function abrir($id = null)
    {
        $this->resetValidation();
        $this->reset(['nombre', 'email', 'role']);
        $this->userId = $id;

        // Obtener roles disponibles
        $this->rolesDisponibles = Role::orderBy('name')->pluck('name')->toArray();

        if ($this->userId) {
            $usuario = User::find($this->userId);
            if ($usuario) {
                $this->nombre = $usuario->name;
                $this->email = $usuario->email;

                // Esto asegura que se seleccione solo el primer rol como string
                $this->role = $usuario->roles->pluck('name')->first();
            }
        }

    }

    public function guardarUser()
    {
        $this->validate();

        if ($this->userId) {
            // Modo edición
            $usuario = User::find($this->userId);
            if ($usuario) {
                $usuario->update([
                    'name' => $this->nombre,
                    'email' => $this->email,
                ]);
                $usuario->syncRoles([$this->role]);
            }
        } else {
            // Modo creación
            $nuevo = User::create([
                'name' => $this->nombre,
                'email' => $this->email,
                'password' => bcrypt('password'), // puedes ajustar esto
            ]);

            $nuevo->assignRole($this->role);
        }

        $this->dispatch('actualiza-lista-usuario');
        $this->dispatch('cerrarModalUser');
    }


    public function render()
    {
        return view('livewire.users.register');
    }

}
