<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Parameter;
use App\Models\ProductImage;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileNotPreviewableException;
use Livewire\WithFileUploads;

class Register extends Component
{
    use WithFileUploads;

    public $productoId = null;
    public $codigo;
    public $nombre;
    public $descripcion;
    public $precio;
    public $precio_promocion;
    public $categoria;
    public $categoriasDisponibles = [];

    public $imagen;
    public $imagenActualUrl;

    protected $rules = [
        'codigo' => 'required|string|max:50',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'precio' => 'required|numeric|min:0',
        'precio_promocion'   => 'nullable|numeric|min:0',
        'categoria' => 'required|integer',
    ];

    protected $messages = [
        'imagen.image' => 'El archivo debe ser una imagen.',
        'imagen.mimes' => 'La imagen debe ser de tipo: jpg, jpeg, png o webp.',
        'imagen.max' => 'La imagen no debe superar los 2MB.',
    ];

    protected $listeners = ['open-modal-product' => 'abrir',
        'setDescripcionSummernote'];

    public function setDescripcionSummernote($data)
    {
        $this->descripcion = $data;
    }

    public function mount()
    {
        $this->categoriasDisponibles = $this->obtenerCategorias();
    }

    public function abrir($id = null, $codigo = null)
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
                $this->precio_promocion = $producto->precio_promocion;
                $this->categoria = $producto->categoria_id;

                $imagen = $producto->imagenes()->where('es_principal', true)->first();
                if ($imagen) {
                    $this->imagenActualUrl = asset('storage/' . $imagen->imagen_url);
                }
            }
        } else {
            $this->reset(['codigo', 'nombre', 'descripcion', 'precio', 'precio_promocion', 'categoria']);
            if ($codigo) {
                $this->codigo = $codigo;
            }
        }

        $this->dispatch('inicializarDescripcion', [
            'descripcion' => $this->descripcion,
        ]);
        $this->dispatch('abrirModalProduct');
    }

    public function updatedImagen()
    {
        if (!$this->imagen->isValid()) {
            $this->addError('imagen', 'El archivo no es válido.');
            return;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mimeType = $this->imagen->getMimeType();

        if (!in_array($mimeType, $allowedMimeTypes)) {
            $this->addError('imagen', 'El archivo seleccionado no es una imagen válida.');
        }
    }

    public function getImagenPreviewUrlProperty()
    {
        try {
            return $this->imagen ? $this->imagen->temporaryUrl() : null;
        } catch (FileNotPreviewableException $e) {
            return null;
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
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
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
                    'precio_promocion' => $this->precio_promocion !== ''
                        ? $this->precio_promocion
                        : null,
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
                'precio_promocion' => $this->precio_promocion !== ''
                    ? $this->precio_promocion
                    : null,
                'stock' => 0,
                'categoria_id' => $this->categoria,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        if ($this->imagen) {
            $anterior = $producto->imagenes()->where('es_principal', true)->first();
            $nuevoPath = $this->imagen->store('productos', 'public');

            if ($anterior) {
                \Storage::disk('public')->delete($anterior->imagen_url);

                $anterior->update([
                    'imagen_url' => $nuevoPath,
                    'auditoriaFechaModificacion' => now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            } else {
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
        return Parameter::where('codigoParametro', 'CATEGORIA')->orderBy('nombre')->pluck('nombre', 'idParametro')->toArray();
    }


    public function render()
    {
        return view('livewire.products.register');
    }
}
