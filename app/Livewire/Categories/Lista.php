<?php

namespace App\Livewire\Categories;

use App\Models\Parameter;
use Livewire\Component;

class Lista extends Component
{
    public function render()
    {
        // Filtrar los parámetros donde el tipo sea "CATEGORIA"
        $categorias = Parameter::where('tipo', 'CATEGORIA')->get();

        return view('livewire.categories.lista', [
            'categorias' => $categorias, // Pasamos los resultados al view
        ]);
    }
}
