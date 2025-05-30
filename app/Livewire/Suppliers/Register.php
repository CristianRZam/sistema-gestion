<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier; // Asumo que tienes este modelo
use Carbon\Carbon;
use Livewire\Component;

class Register extends Component
{
    public $supplierId = null;
    public $nombre;
    public $documento;
    public $telefono;
    public $email;
    public $direccion;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'documento' => 'required|string|max:50|unique:suppliers,documento',
        'telefono' => 'nullable|string|max:50',
        'email' => 'nullable|email|max:255|unique:suppliers,email',
        'direccion' => 'nullable|string|max:255',
    ];

    protected $listeners = ['open-modal' => 'abrir'];

    public function abrir($id = null)
    {
        $this->resetValidation();
        $this->supplierId = $id;

        if ($this->supplierId) {
            $supplier = Supplier::find($this->supplierId);
            if ($supplier) {
                $this->nombre = $supplier->nombre;
                $this->documento = $supplier->documento;
                $this->telefono = $supplier->telefono;
                $this->email = $supplier->email;
                $this->direccion = $supplier->direccion;
            }
        } else {
            $this->reset(['nombre', 'documento', 'telefono', 'email', 'direccion']);
        }
    }

    public function guardarSupplier()
    {
        // Ajustar reglas para unique ignorando el registro actual en edición
        $rules = $this->rules;

        if ($this->supplierId) {
            $rules['documento'] = 'required|string|max:50|unique:suppliers,documento,' . $this->supplierId;
            $rules['email'] = 'nullable|email|max:255|unique:suppliers,email,' . $this->supplierId;
        }

        $this->validate($rules);

        $userId = auth()->id();

        if ($this->supplierId) {
            // Edición
            $supplier = Supplier::find($this->supplierId);

            if ($supplier) {
                $supplier->update([
                    'nombre' => $this->nombre,
                    'documento' => $this->documento,
                    'telefono' => $this->telefono,
                    'email' => $this->email,
                    'direccion' => $this->direccion,
                    'auditoriaFechaModificacion' => Carbon::now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            }
        } else {
            // Creación
            Supplier::create([
                'nombre' => $this->nombre,
                'documento' => $this->documento,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        $this->dispatch('actualiza-lista-supplier');
        $this->dispatch('cerrarModalSupplier');
    }

    public function render()
    {
        return view('livewire.suppliers.register');
    }
}
