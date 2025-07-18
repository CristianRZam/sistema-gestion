<?php

namespace App\Livewire\Services;


use App\Models\Service;
use App\Models\ServiceImage;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileNotPreviewableException;
use Livewire\WithFileUploads;

class Register extends Component
{
    use WithFileUploads;
    public $serviceId = null;
    public $nombre;
    public $precio;
    public $descripcion;
    public $imagen;
    public $imagenActualUrl;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'precio' => 'required|numeric|min:0',
        'descripcion' => 'nullable|string',
    ];

    protected $messages = [
        'imagen.image' => 'El archivo debe ser una imagen.',
        'imagen.mimes' => 'La imagen debe ser de tipo: jpg, jpeg, png o webp.',
        'imagen.max' => 'La imagen no debe superar los 2MB.',
    ];

    protected $listeners = ['open-modal-service' => 'abrir',
        'setDescripcionSummernote'];

    public function setDescripcionSummernote($data)
    {
        $this->descripcion = $data;
    }

    public function abrir($id = null)
    {
        $this->resetValidation();
        $this->serviceId = $id;

        if ($this->serviceId) {
            $servicio = Service::find($this->serviceId);
            if ($servicio) {
                $this->nombre = $servicio->nombre;
                $this->precio = $servicio->precio;
                $this->descripcion = $servicio->descripcion;

                $imagen = $servicio->imagenes()->where('es_principal', true)->first();
                if ($imagen) {
                    $this->imagenActualUrl = asset('storage/' . $imagen->imagen_url);
                }
            }
        } else {
            $this->reset(['nombre', 'precio', 'descripcion']);
        }

        $this->dispatch('inicializarDescripcion', [
            'descripcion' => $this->descripcion,
        ]);

        $this->dispatch('abrirModalService');
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
            ...($this->serviceId
                ? array_merge($this->rules, [
                    'nombre' => 'required|string|max:255|unique:services,nombre,' . $this->serviceId,
                ])
                : array_merge($this->rules, [
                    'nombre' => 'required|string|max:255|unique:services,nombre',
                ])),
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $userId = auth()->id();

        if ($this->serviceId) {
            $service = Service::find($this->serviceId);
            if ($service) {
                $service->update([
                    'nombre' => $this->nombre,
                    'precio' => $this->precio,
                    'descripcion' => $this->descripcion,
                    'auditoriaFechaModificacion' => Carbon::now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            }
        } else {
            $service = Service::create([
                'nombre' => $this->nombre,
                'precio' => $this->precio,
                'descripcion' => $this->descripcion,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        if ($this->imagen) {
            $anterior = $service->imagenes()->where('es_principal', true)->first();
            $nuevoPath = $this->imagen->store('servicios', 'public');

            if ($anterior) {
                \Storage::disk('public')->delete($anterior->imagen_url);

                $anterior->update([
                    'imagen_url' => $nuevoPath,
                    'auditoriaFechaModificacion' => now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            } else {
                ServiceImage::create([
                    'service_id' => $service->id,
                    'imagen_url' => $nuevoPath,
                    'es_principal' => true,
                    'auditoriaFechaCreacion' => now(),
                    'auditoriaCreadoPor' => $userId,
                ]);
            }
        }

        $this->dispatch('actualiza-lista-servicio');
        $this->dispatch('cerrarModalService');
    }
    public function render()
    {
        return view('livewire.services.register');
    }
}
