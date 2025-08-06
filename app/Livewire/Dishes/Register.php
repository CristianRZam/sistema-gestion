<?php

namespace App\Livewire\Dishes;

use App\Models\Dish;
use App\Models\DishImage;
use App\Models\Parameter;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileNotPreviewableException;
use Livewire\WithFileUploads;

class Register extends Component
{
    use WithFileUploads;

    public $platilloId = null;
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

    protected $listeners = ['open-modal-dish' => 'abrir',
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
        $this->platilloId = $id;

        $this->categoriasDisponibles = $this->obtenerCategorias();
        $this->imagen = null;
        $this->imagenActualUrl = null;

        if ($this->platilloId) {
            $platillo = Dish::find($this->platilloId);
            if ($platillo) {
                $this->codigo = $platillo->codigo;
                $this->nombre = $platillo->nombre;
                $this->descripcion = $platillo->descripcion;
                $this->precio = $platillo->precio;
                $this->precio_promocion = $platillo->precio_promocion;
                $this->categoria = $platillo->categoria_id;

                $imagen = $platillo->imagenes()->where('es_principal', true)->first();
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
        $this->dispatch('abrirModalDish');
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


    public function guardar()
    {
        $this->validate([
            ...($this->platilloId
                ? array_merge($this->rules, [
                    'codigo' => 'required|string|max:50|unique:dishes,codigo,' . $this->platilloId,
                ])
                : array_merge($this->rules, [
                    'codigo' => 'required|string|max:50|unique:dishes,codigo',
                ])),
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $userId = auth()->id();

        if ($this->platilloId) {
            $platillo= Dish::find($this->platilloId);
            if ($platillo) {
                $platillo->update([
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
            $platillo = Dish::create([
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'precio_promocion' => $this->precio_promocion !== ''
                    ? $this->precio_promocion
                    : null,
                'categoria_id' => $this->categoria,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        if ($this->imagen) {
            $anterior = $platillo->imagenes()->where('es_principal', true)->first();
            $nuevoPath = $this->imagen->store('platillos', 'public');

            if ($anterior) {
                \Storage::disk('public')->delete($anterior->imagen_url);

                $anterior->update([
                    'imagen_url' => $nuevoPath,
                    'auditoriaFechaModificacion' => now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            } else {
                DishImage::create([
                    'dish_id' => $platillo->id,
                    'imagen_url' => $nuevoPath,
                    'es_principal' => true,
                    'auditoriaFechaCreacion' => now(),
                    'auditoriaCreadoPor' => $userId,
                ]);
            }
        }

        $this->dispatch('actualiza-lista-platillo');
        $this->dispatch('cerrarModalDish');
    }

    public function obtenerCategorias()
    {
        return Parameter::where('codigoParametro', 'CATEGORIA_PLATILLO')->orderBy('nombre')->pluck('nombre', 'idParametro')->toArray();
    }


    public function render()
    {
        return view('livewire.dishes.register');
    }
}
