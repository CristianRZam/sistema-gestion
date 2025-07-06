<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $customerIdToDelete;

    // Filtros
    public $nombreFiltro = '';

    public $numeroDocumentoFiltro = '';

    protected $listeners = [
        'actualiza-lista-customer' => '$refresh',
        'open-modal-delete-customer' => 'setCustomerIdToDelete',
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function actualizarFiltros($filtros)
    {
        $this->nombreFiltro = $filtros['nombre'] ?? '';
        $this->numeroDocumentoFiltro = $filtros['numero_documento'] ?? '';
        $this->resetPage(); // Reinicia la paginación al aplicar nuevos filtros
    }

    /**
     * Renderiza la vista con la lista actualizada de clientes.
     */
    public function render()
    {
        $query = Customer::query()->whereNull('auditoriaFechaEliminacion');

        // Filtro por nombre
        if (!empty($this->nombreFiltro)) {
            $query->where('nombre', 'like', '%' . $this->nombreFiltro . '%');
        }

        // Filtro por documento
        if (!empty($this->numeroDocumentoFiltro)) {
            $query->where('documento', 'like', '%' . $this->numeroDocumentoFiltro . '%');
        }

        $customers = $query->paginate(10);

        return view('livewire.customers.lista', [
            'customers' => $customers,
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

