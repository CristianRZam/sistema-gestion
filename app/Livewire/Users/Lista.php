<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;

class Lista extends Component
{

    public $usuarios;

    protected $listeners = [
        'actualiza-lista-usuario' => 'actualizarUsuarios',
    ];

    public function mount()
    {
        $this->actualizarUsuarios(); // Cargar los roles al iniciar
    }

    public function actualizarUsuarios()
    {
        $this->usuarios = User::with('roles')->get();
    }


    public function render()
    {
        return view('livewire.users.lista', [
            'usuarios' => $this->usuarios,
        ]);
    }

}
