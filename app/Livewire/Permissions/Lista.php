<?php

namespace App\Livewire\Permissions;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Lista extends Component
{
    public Role $role;
    public array $permissions = [];
    public array $selectedPermissions = [];

    public ?string $message = null; // <-- propiedad para el mensaje

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->permissions = Permission::all()->toArray();
        $this->selectedPermissions = $role->permissions()->pluck('name')->toArray();
    }

    public function syncPermissions()
    {
        $this->role->syncPermissions($this->selectedPermissions);
        $this->message = 'Permisos actualizados correctamente.';
    }

    public function clearMessage()
    {
        $this->message = null;
    }

    public function render()
    {
        return view('livewire.permissions.lista');
    }
}
