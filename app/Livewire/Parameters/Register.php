<?php

namespace App\Livewire\Parameters;

use App\Models\Parameter;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;

class Register extends Component
{
    use WithFileUploads;
    public $parametroId = null;
    public $nombre;
    public $nombreCorto;
    public $orden;
    public $tipo;
    public $codigoParametro;
    public $imagen;
    public $imagenActualUrl;



    protected function rules()
    {
        $rules = [
            'nombreCorto' => 'required|string|max:100',
            'orden' => 'required|integer|min:1',
            'codigoParametro' => 'required|string|max:100',
            'tipo' => 'required',
        ];

        if ($this->tipo == '1') {
            $rules['imagen'] = $this->parametroId ? 'nullable|image|max:1024' : 'required|image|max:1024';
        } elseif ($this->tipo == '4') {
            $rules['imagen'] = $this->parametroId
                ? 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048'
                : 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:2048';
        } else {
            $rules['nombre'] = 'required|string|max:255';
        }

        return $rules;
    }


    protected $listeners = ['open-modal' => 'abrir'];

    public function abrir($id = null)
    {

        $this->resetValidation();

        $this->parametroId = $id;

        if ($this->parametroId) {
            $parametro = Parameter::find($this->parametroId);
            if ($parametro) {
                $this->nombre = $parametro->nombre;
                $this->nombreCorto = $parametro->nombreCorto;
                $this->orden = $parametro->orden;
                $this->tipo = $parametro->tipo;
                $this->codigoParametro = $parametro->codigoParametro;

                if ($this->tipo == '1' && $parametro->nombre) {
                    $this->imagenActualUrl = \Storage::url($parametro->nombre); // 👈 Esto carga la imagen actual
                }
            }

        } else {
            $this->reset(['nombre', 'nombreCorto', 'orden', 'tipo', 'codigoParametro']);
        }

    }


    public function guardarParametro()
    {
        $this->validate();


        $userId = auth()->id();

        if ($this->parametroId) {
            // Modo edición
            $parametro = Parameter::find($this->parametroId);

            if ($parametro) {
                // Verificar si cambió el tipo
                if ($parametro->tipo !== $this->tipo) {
                    $nuevoIdParametro = Parameter::where('codigoParametro', $this->codigoParametro)->max('idParametro') + 1;
                    $parametro->idParametro = $nuevoIdParametro;
                }

                // Si tipo es imagen y hay nueva imagen, reemplazarla
                if (in_array($this->tipo, ['1', '4']) && $this->imagen) {
                    if ($parametro->nombre && \Storage::disk('public')->exists($parametro->nombre)) {
                        \Storage::disk('public')->delete($parametro->nombre);
                    }

                    $carpeta = $this->tipo == '1' ? 'imagenesParametros' : 'archivosParametros';
                    $ruta = $this->imagen->store($carpeta, 'public');
                    $this->nombre = $ruta;
                } elseif ($this->tipo != '1' && $this->tipo != '4') {
                    $this->nombre = $this->nombre;
                }


                // ✅ Siempre pasar nombre en el update
                $parametro->update([
                    'nombre' => $this->nombre,
                    'nombreCorto' => $this->nombreCorto,
                    'orden' => $this->orden,
                    'tipo' => $this->tipo,
                    'codigoParametro' => $this->codigoParametro,
                    'auditoriaFechaModificacion' => Carbon::now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            }
        } else {
            // Modo creación
            $siguienteIdParametro = Parameter::where('codigoParametro', $this->codigoParametro)->max('idParametro') + 1;

            if (in_array($this->tipo, ['1', '4']) && $this->imagen) {
                $carpeta = $this->tipo == '1' ? 'imagenesParametros' : 'archivosParametros';
                $this->nombre = $this->imagen->store($carpeta, 'public');
            }


            Parameter::create([
                'idParametro' => $siguienteIdParametro,
                'tipo' => $this->tipo,
                'codigoParametro' => $this->codigoParametro,
                'nombre' => $this->nombre,
                'nombreCorto' => $this->nombreCorto,
                'orden' => $this->orden,
                'auditoriaFechaCreacion' => Carbon::now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }

        $this->dispatch('actualiza-lista-parametro');
        $this->dispatch('cerrarModal');
    }


    public function render()
    {
        $tipos = Parameter::where('codigoParametro', 'TIPO_PARAMETRO')->orderBy('orden')->get();

        return view('livewire.parameters.register', [
            'tipos' => $tipos,
        ]);
    }

}
