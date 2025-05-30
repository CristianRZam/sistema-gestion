<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Lista extends Component
{
    public $customers;
    public $customerIdToDelete;

    protected $listeners = [
        'actualiza-lista-customer' => 'actualizarCustomers',
        'open-modal-delete-customer' => 'setCustomerIdToDelete',
    ];

    /**
     * Actualiza la lista de clientes cargando solo los activos (sin eliminación lógica).
     */
    public function actualizarCustomers()
    {
        $this->customers = Customer::whereNull('auditoriaFechaEliminacion')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Renderiza la vista con la lista actualizada de clientes.
     */
    public function render()
    {
        $this->actualizarCustomers();

        return view('livewire.customers.lista', [
            'customers' => $this->customers,
        ]);
    }

    /**
     * Establece el ID del cliente a eliminar, usado para la confirmación modal.
     */
    public function setCustomerIdToDelete($id = null)
    {
        $this->customerIdToDelete = $id;
    }

    /**
     * Realiza la eliminación lógica del cliente seleccionado.
     */
    public function deleteCustomer()
    {
        if (!$this->customerIdToDelete) {
            session()->flash('error', 'Cliente no válido.');
            return;
        }

        $customer = Customer::find($this->customerIdToDelete);

        if (!$customer) {
            session()->flash('error', 'Cliente no encontrado.');
            return;
        }

        $customer->update([
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor' => Auth::id(),
        ]);

        //session()->flash('success', 'Cliente eliminado correctamente.');

        $this->dispatch('actualiza-lista-customer');
        $this->dispatch('cerrarModalDeteleCustomer');
        $this->reset('customerIdToDelete');
    }
}

