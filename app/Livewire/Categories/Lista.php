<?php

namespace App\Livewire\Categories;

use App\Models\Parameter;
use Livewire\Component;

class Lista extends Component
{
    public function render()
    {
        $categorias = Parameter::where('tipo', 'CATEGORIA')->get();

        return view('livewire.categories.lista', [
            'categorias' => $categorias,
        ]);
    }
}
