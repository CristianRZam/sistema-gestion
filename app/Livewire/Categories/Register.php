<?php

namespace App\Livewire\Categories;

use App\Models\Parameter;
use Livewire\Component;

class Register extends Component
{
    public $show = true;
    public $nombre;
    public $nombreCorto;
    public $orden;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'nombreCorto' => 'required|string|max:100',
        'orden' => 'required|integer|min:1',
    ];

    #[On('abrir-modal-categoria')]
    public function abrir()
    {
        $this->reset(['nombre', 'nombreCorto', 'orden']);
        $this->show = true;
    }

    public function guardarCategoria()
    {
        $this->validate();

        Parameter::create([
            'nombre' => $this->nombre,
            'nombreCorto' => $this->nombreCorto,
            'orden' => $this->orden,
        ]);

        $this->dispatch('categoriaGuardada');
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.categories.register');
    }
}
