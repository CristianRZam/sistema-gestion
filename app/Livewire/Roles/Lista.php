<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'actualiza-lista-role' => '$refresh',
    ];

    public function render()
    {
        return view('livewire.roles.lista', [
            'roles' => Role::orderBy('name')->paginate(10),
        ]);
    }
}
