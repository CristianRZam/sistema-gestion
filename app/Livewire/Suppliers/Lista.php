<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier; // Asegúrate de tener este modelo
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Lista extends Component
{
    public $suppliers;
    public $supplierIdToDelete;

    protected $listeners = [
        'actualiza-lista-supplier' => 'actualizarSuppliers',
        'open-modal-delete-supplier' => 'setSupplierIdToDelete',
    ];

    /**
     * Actualiza la lista de proveedores cargando solo los activos (sin eliminación lógica).
     */
    public function actualizarSuppliers()
    {
        $this->suppliers = Supplier::whereNull('auditoriaFechaEliminacion')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Renderiza la vista con la lista actualizada de proveedores.
     */
    public function render()
    {
        $this->actualizarSuppliers();

        return view('livewire.suppliers.lista', [
            'suppliers' => $this->suppliers,
        ]);
    }

    /**
     * Establece el ID del proveedor a eliminar, usado para la confirmación modal.
     */
    public function setSupplierIdToDelete($id = null)
    {
        $this->supplierIdToDelete = $id;
    }

    /**
     * Realiza la eliminación lógica del proveedor seleccionado.
     */
    public function deleteSupplier()
    {
        if (!$this->supplierIdToDelete) {
            session()->flash('error', 'Proveedor no válido.');
            return;
        }

        $supplier = Supplier::find($this->supplierIdToDelete);

        if (!$supplier) {
            session()->flash('error', 'Proveedor no encontrado.');
            return;
        }

        $supplier->update([
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor' => Auth::id(),
        ]);

        //session()->flash('success', 'Proveedor eliminado correctamente.');

        $this->dispatch('actualiza-lista-supplier');
        $this->dispatch('cerrarModalDeleteSupplier');
        $this->reset('supplierIdToDelete');
    }
}
