<?php

namespace App\Livewire\Categories;

use App\Models\Parameter;
use Carbon\Carbon;
use Livewire\Component;

class Register extends Component
{
    public $categoriaId = null;
    public $nombre;
    public $nombreCorto;
    public $orden;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'nombreCorto' => 'required|string|max:100',
        'orden' => 'required|integer|min:1',
    ];

    protected $listeners = ['open-modal' => 'abrir'];

    public function abrir($id = null)
    {

        $this->resetValidation();

        $this->categoriaId = $id;

        if ($this->categoriaId) {
            $categoria = Parameter::find($this->categoriaId);
            if ($categoria) {
                $this->nombre = $categoria->nombre;
                $this->nombreCorto = $categoria->nombreCorto;
                $this->orden = $categoria->orden;
            }
        } else {
            $this->reset(['nombre', 'nombreCorto', 'orden']);
        }
    }


    public function guardarCategoria()
    {
        $this->validate();

        $userId = auth()->id();
        if ($this->categoriaId) {
            // Modo edición
            $categoria = Parameter::find($this->categoriaId);

            if ($categoria) {
                $categoria->update([
                    'nombre' => $this->nombre,
                    'nombreCorto' => $this->nombreCorto,
                    'orden' => $this->orden,
                    'auditoriaFechaModificacion' => Carbon::now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            }
        } else {
            // Modo creación
            // Obtener el siguiente idParametro para tipo CATEGORIA
            $siguienteIdParametro = Parameter::where('tipo', 'CATEGORIA')->max('idParametro') + 1;

            Parameter::create([
                'idParametro' => $siguienteIdParametro,
                'tipo' => 'CATEGORIA',
                'nombre' => $this->nombre,
                'nombreCorto' => $this->nombreCorto,
                'orden' => $this->orden,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        $this->dispatch('actualiza-lista-categoria');
        $this->dispatch('cerrarModal');
    }


    public function render()
    {
        return view('livewire.categories.register');
    }
}
