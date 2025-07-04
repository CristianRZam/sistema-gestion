<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    public $selectedProducts = [];        // IDs seleccionados
    public $selectAll = false;
    protected $paginationTheme = 'tailwind'; // Puedes usar 'bootstrap' si lo prefieres

    public $productIdToDelete;
    public $codigoEscaneado = '';

    // Filtros
    public $nombreFiltro = '';
    public $categoriaFiltro = []; // ← Ya lo estás haciendo bien

    public $stockFiltro = '';

    protected $listeners = [
        'actualiza-lista-producto' => '$refresh',
        'open-modal-delete-product' => 'setProductIdToDelete',
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function updatedSelectAll($value)
    {
        $this->selectedProducts = [];
        if ($value) {
            // Query con tus filtros idénticos al render()
            $query = Product::query()->whereNull('auditoriaFechaEliminacion');

            if ($this->nombreFiltro) {
                $query->where('nombre', 'like', '%'.$this->nombreFiltro.'%');
            }
            if (!empty($this->categoriaFiltro)) {
                $query->whereIn('categoria_id', $this->categoriaFiltro);
            }
            if (is_numeric($this->stockFiltro) && $this->stockFiltro > 0) {
                $query->where('stock', '>=', $this->stockFiltro);
            }

            // Pluck de **todos** los IDs filtrados
            $this->selectedProducts = $query->pluck('id')->toArray();
        }
    }

    public function exportSelected()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'No hay productos seleccionados.');
            return;
        }

        // Aquí generas tu Excel/PDF usando sólo los IDs de $this->selectedProducts
        // Por ejemplo, rediriges a una ruta que reciba ?ids[]=1&ids[]=2…
        return response()->streamDownload(function() {
            // Lógica de generación de archivo...
        }, 'productos_seleccionados.xlsx');
    }

    public function render()
    {
        $query = Product::query()->whereNull('auditoriaFechaEliminacion');

        if ($this->nombreFiltro) {
            $query->where('nombre', 'like', '%' . $this->nombreFiltro . '%');
        }

        if (!empty($this->categoriaFiltro)) {
            $query->whereIn('categoria_id', $this->categoriaFiltro);
        }


        if (is_numeric($this->stockFiltro) && $this->stockFiltro > 0) {
            $query->where('stock', '>=', $this->stockFiltro);
        }

        $productos = $query->paginate(10);


        return view('livewire.products.lista', [
            'productos' => $productos,
        ]);

    }

    public function actualizarFiltros($filtros)
    {
        $this->nombreFiltro = $filtros['nombre'] ?? '';
        $this->categoriaFiltro = $filtros['categoria_ids'] ?? '';
        $this->stockFiltro = $filtros['stock'] ?? '';
        $this->selectedProducts = [];
        $this->selectAll = false;
        $this->resetPage(); // Reinicia la paginación al aplicar nuevos filtros
    }

    public function setProductIdToDelete($id = null)
    {
        $this->productIdToDelete = $id;
    }

    public function deleteProduct()
    {
        if (!$this->productIdToDelete) {
            session()->flash('error', 'Producto no válido.');
            return;
        }

        $product = Product::find($this->productIdToDelete);

        if (!$product) {
            session()->flash('error', 'Producto no encontrado.');
            return;
        }

        $product->update([
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor' => Auth::id(),
        ]);

        $this->dispatch('actualiza-lista-producto');
        $this->dispatch('cerrarModalDeteleProduct');
        $this->reset('productIdToDelete');
    }

    public function procesarCodigoEscaneado()
    {
        $codigo = $this->codigoEscaneado;

        $producto = Product::where('codigo', $codigo)->first();


        if (!$producto) {
            session()->flash('error', 'Producto no encontrado.');
            $this->dispatch('abrirModalScaneo');
            $this->dispatch('open-modal-product', codigo: $codigo);
            return;
        }


        $this->dispatch('abrirModalScaneo');
        $this->dispatch('open-modal-product', id: $producto->id);

    }


}
