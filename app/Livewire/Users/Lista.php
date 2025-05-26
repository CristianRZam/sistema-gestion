<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;

class Lista extends Component
{
    public function render()
    {
        $usuarios = User::with('roles')->get();

        return view('livewire.users.lista', [
            'usuarios' => $usuarios,
        ]);
    }
}
