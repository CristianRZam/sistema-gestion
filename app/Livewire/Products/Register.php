<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Parameter;
use App\Models\ProductImage;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;

class Register extends Component
{
    use WithFileUploads;
    public $productoId = null;
    public $codigo;
    public $nombre;
    public $descripcion;
    public $precio;
    public $stock;
    public $categoria; // Este será el ID lógico de la categoría

    public $categoriasDisponibles = [];

    // ... tus propiedades anteriores
    public $imagen; // Imagen cargada
    public $imagenActualUrl; // URL para previsualizar si ya existe

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
        $this->imagen = null;
        $this->imagenActualUrl = null;

        if ($this->productoId) {
            $producto = Product::find($this->productoId);
            if ($producto) {
                $this->codigo = $producto->codigo;
                $this->nombre = $producto->nombre;
                $this->descripcion = $producto->descripcion;
                $this->precio = $producto->precio;
                $this->stock = $producto->stock;
                $this->categoria = $producto->categoria_id;

                $imagen = $producto->imagenes()->where('es_principal', true)->first();
                if ($imagen) {
                    $this->imagenActualUrl = asset('storage/' . $imagen->imagen_url);
                }
            }
        } else {
            $this->reset(['codigo', 'nombre', 'descripcion', 'precio', 'stock', 'categoria']);
        }
    }

    public function guardarProducto()
    {
        $this->validate([
            ...($this->productoId
                ? array_merge($this->rules, [
                    'codigo' => 'required|string|max:50|unique:products,codigo,' . $this->productoId,
                ])
                : array_merge($this->rules, [
                    'codigo' => 'required|string|max:50|unique:products,codigo',
                ])),
            'imagen' => 'nullable|image|max:2048', // máx 2MB
        ]);
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
            $producto = Product::create([
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


        // Si se subió imagen
        if ($this->imagen) {
            $anterior = $producto->imagenes()->where('es_principal', true)->first();

            // Guarda nueva imagen
            $nuevoPath = $this->imagen->store('productos', 'public');

            if ($anterior) {
                // Elimina el archivo antiguo
                \Storage::disk('public')->delete($anterior->imagen_url);

                // Actualiza el registro existente
                $anterior->update([
                    'imagen_url' => $nuevoPath,
                    'auditoriaFechaModificacion' => now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            } else {
                // Si no había una imagen principal, crea una nueva
                ProductImage::create([
                    'product_id' => $producto->id,
                    'imagen_url' => $nuevoPath,
                    'es_principal' => true,
                    'auditoriaFechaCreacion' => now(),
                    'auditoriaCreadoPor' => $userId,
                ]);
            }
        }
        $this->dispatch('actualiza-lista-producto');
        $this->dispatch('cerrarModalProduct');
    }

    public function obtenerCategorias()
    {
        // Obtenemos categorías desde la tabla parameters (codigoParametro = CATEGORIA)
        return Parameter::where('codigoParametro', 'CATEGORIA')->orderBy('nombre')->pluck('nombre', 'idParametro')->toArray();
    }

    public function render()
    {
        return view('livewire.products.register');
    }
}
