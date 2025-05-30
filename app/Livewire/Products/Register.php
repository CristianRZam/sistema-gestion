<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Parameter;
use Carbon\Carbon;
use Livewire\Component;

class Register extends Component
{
    public $productoId = null;
    public $codigo;
    public $nombre;
    public $descripcion;
    public $precio;
    public $stock;
    public $categoria; // Este será el ID lógico de la categoría

    public $categoriasDisponibles = [];

    protected $rules = [
        'codigo' => 'required|string|max:50',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string|max:1000',
        'precio' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'categoria' => 'required|integer',
    ];

    protected $listeners = ['open-modal-product' => 'abrir'];

    public function mount()
    {
        $this->categoriasDisponibles = $this->obtenerCategorias();
    }


    public function abrir($id = null)
    {
        $this->resetValidation();
        $this->productoId = $id;

        $this->categoriasDisponibles = $this->obtenerCategorias();

        if ($this->productoId) {
            $producto = Product::find($this->productoId);
            if ($producto) {
                $this->codigo = $producto->codigo;
                $this->nombre = $producto->nombre;
                $this->descripcion = $producto->descripcion;
                $this->precio = $producto->precio;
                $this->stock = $producto->stock;
                $this->categoria = $producto->categoria_id;
            }
        } else {
            $this->reset(['codigo', 'nombre', 'descripcion', 'precio', 'stock', 'categoria']);
        }
    }

    public function guardarProducto()
    {
        $this->validate(
            $this->productoId
                ? array_merge($this->rules, [
                'codigo' => 'required|string|max:50|unique:products,codigo,' . $this->productoId,
            ])
                : array_merge($this->rules, [
                'codigo' => 'required|string|max:50|unique:products,codigo',
            ])
        );

        $userId = auth()->id();

        if ($this->productoId) {
            $producto = Product::find($this->productoId);
            if ($producto) {
                $producto->update([
                    'codigo' => $this->codigo,
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'precio' => $this->precio,
                    'stock' => $this->stock,
                    'categoria_id' => $this->categoria,
                    'auditoriaFechaModificacion' => Carbon::now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            }
        } else {
            Product::create([
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'stock' => $this->stock,
                'categoria_id' => $this->categoria,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        $this->dispatch('actualiza-lista-producto');
        $this->dispatch('cerrarModalProduct');
    }

    public function obtenerCategorias()
    {
        // Obtenemos categorías desde la tabla parameters (tipo = CATEGORIA)
        return Parameter::where('tipo', 'CATEGORIA')->orderBy('nombre')->pluck('nombre', 'idParametro')->toArray();
    }

    public function render()
    {
        return view('livewire.products.register');
    }
}
