<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier; // Asegúrate de tener este modelo
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';
    public $supplierIdToDelete;

    // Filtros
    public $nombreFiltro = '';
    public $numeroDocumentoFiltro = '';

    protected $listeners = [
        'actualiza-lista-supplier' => '$refresh',
        'open-modal-delete-supplier' => 'setSupplierIdToDelete',
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function actualizarFiltros($filtros)
    {
        $this->nombreFiltro = $filtros['nombre'] ?? '';
        $this->numeroDocumentoFiltro = $filtros['numero_documento'] ?? '';
        $this->resetPage(); // Reinicia la paginación al aplicar nuevos filtros
    }


    /**
     * Renderiza la vista con la lista actualizada de proveedores.
     */
    public function render()
    {
        $query = Supplier::query()->whereNull('auditoriaFechaEliminacion');

        // Filtro por nombre
        if (!empty($this->nombreFiltro)) {
            $query->where('nombre', 'like', '%' . $this->nombreFiltro . '%');
        }

        // Filtro por documento
        if (!empty($this->numeroDocumentoFiltro)) {
            $query->where('documento', 'like', '%' . $this->numeroDocumentoFiltro . '%');
        }

        $suppliers = $query->paginate(10);

        return view('livewire.suppliers.lista', [
            'suppliers' => $suppliers,
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

        $this->dispatch('actualiza-lista-supplier');
        $this->dispatch('cerrarModalDeleteSupplier');
        $this->reset('supplierIdToDelete');
    }
}
