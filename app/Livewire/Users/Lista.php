<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind'; // O bootstrap si usas Bootstrap

    protected $listeners = [
        'actualiza-lista-usuario' => '$refresh',
    ];

    public function updatingPage()
    {
        // Para reiniciar a la primera página al hacer búsqueda o actualización
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.users.lista', [
            'usuarios' => User::with('roles')->paginate(10),
        ]);
    }
}
