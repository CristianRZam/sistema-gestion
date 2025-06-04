<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Carbon\Carbon;
use Livewire\Component;

class Register extends Component
{
    public $customerId = null;
    public $nombre;
    public $documento;
    public $telefono;
    public $email;
    public $direccion;

    public $modo = 'crud'; // 'crud' o 'venta'
    public $clienteEncontrado = null;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'documento' => 'required|string|max:50|unique:customers,documento',
        'telefono' => 'nullable|string|max:50',
        'email' => 'nullable|email|max:255|unique:customers,email',
        'direccion' => 'nullable|string|max:255',
    ];

    protected $listeners = ['open-modal-customer' => 'abrir'];

    public function abrir($id = null, $modo = 'crud')
    {
        $this->resetValidation();
        $this->modo = $modo;
        $this->customerId = $id;

        if ($this->modo === 'venta') {
            $this->reset(['nombre', 'documento', 'telefono', 'email', 'direccion']);
            // Aquí podrías activar automáticamente un input para buscar por DNI
            return;
        }

        if ($this->customerId) {
            $customer = Customer::find($this->customerId);
            if ($customer) {
                $this->nombre = $customer->nombre;
                $this->documento = $customer->documento;
                $this->telefono = $customer->telefono;
                $this->email = $customer->email;
                $this->direccion = $customer->direccion;
            }
        } else {
            $this->reset(['nombre', 'documento', 'telefono', 'email', 'direccion']);
        }
    }


    public function guardarCustomer()
    {
        // Ajustar reglas para unique ignorando el actual registro en edición
        $rules = $this->rules;

        if ($this->customerId) {
            $rules['documento'] = 'required|string|max:50|unique:customers,documento,' . $this->customerId;
            $rules['email'] = 'nullable|email|max:255|unique:customers,email,' . $this->customerId;
        }

        $this->validate($rules);

        $userId = auth()->id();

        if ($this->customerId) {
            // Edición
            $customer = Customer::find($this->customerId);

            if ($customer) {
                $customer->update([
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
            Customer::create([
                'nombre' => $this->nombre,
                'documento' => $this->documento,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        if ($this->modo === 'venta') {
            $cliente = Customer::where('documento', $this->documento)->first();
            $this->dispatch('clienteSeleccionadoDesdeVenta', $cliente->id);
        } else {
            $this->dispatch('actualiza-lista-customer');
        }

        $this->dispatch('cerrarModalCustomer');

    }

    public function buscarClientePorDocumento()
    {
        $this->resetValidation();

        $this->clienteEncontrado = Customer::where('documento', $this->documento)->first();

        if ($this->clienteEncontrado) {
            $this->dispatch('clienteSeleccionadoDesdeVenta', $this->clienteEncontrado->id);
            $this->dispatch('cerrarModalCustomer');
        } else {
            $this->addError('documento', 'Cliente no encontrado. Por favor completa el formulario para registrarlo.');
        }
    }


    public function render()
    {
        return view('livewire.customers.register');
    }
}
