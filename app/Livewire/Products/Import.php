<?php

namespace App\Livewire\Products;

use App\Models\Parameter;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\UploadedFile;
use App\Models\ProductImage;

class Import extends Component
{
    use WithFileUploads;

    public $excelFile;
    public $imagenesZip;

    protected $listeners = ['open-modal-product-import' => 'resetearCampos'];

    public function resetearCampos()
    {
        $this->reset(['excelFile', 'imagenesZip']);
        $this->resetValidation();
        $this->dispatch('abrirModalProductImport');
    }

    public function guardarProducto()
    {
        $this->validate([
            'excelFile' => 'nullable|file|mimes:xlsx,xls',
            'imagenesZip' => 'nullable|file|mimes:zip|max:102400', // 100MB
        ]);

        if (!$this->excelFile && !$this->imagenesZip) {
            $this->addError('excelFile', 'Debe subir el archivo Excel.');
            $this->addError('imagenesZip', 'Debe subir el archivo ZIP de imágenes.');
            return;
        }

        $userId = auth()->id();
        $rows = [];

        // Leer Excel
        if ($this->excelFile instanceof UploadedFile) {
            $spreadsheet = IOFactory::load($this->excelFile->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        }

        // Validar filas del Excel antes de guardar
        foreach (array_slice($rows, 1) as $index => $row) {
            $linea = $index + 2;

            $codigo = trim($row['A'] ?? '');
            $nombre = trim($row['B'] ?? '');
            $descripcion = $row['C'] ?? '';
            $precio = $row['D'] ?? '';
            $categoria = trim($row['E'] ?? '');

            if (empty($codigo)) {
                $this->dispatch('cerrarModalProductImport');
                $this->dispatch('mostrarErrorImportacion', [
                    'mensaje' => "Fila $linea: El código es obligatorio."
                ]);
                return;
            }

            if (strlen($codigo) > 255) {
                $this->dispatch('cerrarModalProductImport');
                $this->dispatch('mostrarErrorImportacion', [
                    'mensaje' => "Fila $linea: El código excede 255 caracteres."
                ]);
                return;
            }

            if (empty($nombre)) {
                $this->dispatch('cerrarModalProductImport');
                $this->dispatch('mostrarErrorImportacion', [
                    'mensaje' => "Fila $linea: El nombre es obligatorio."
                ]);
                return;
            }

            if (strlen($nombre) > 255) {
                $this->dispatch('cerrarModalProductImport');
                $this->dispatch('mostrarErrorImportacion', [
                    'mensaje' => "Fila $linea: El nombre excede 255 caracteres."
                ]);
                return;
            }

            if (!is_numeric($precio)) {
                $this->dispatch('cerrarModalProductImport');
                $this->dispatch('mostrarErrorImportacion', [
                    'mensaje' => "Fila $linea: El precio debe ser numérico."
                ]);
                return;
            }

            if ((float)$precio < 0) {
                $this->dispatch('cerrarModalProductImport');
                $this->dispatch('mostrarErrorImportacion', [
                    'mensaje' => "Fila $linea: El precio no puede ser negativo."
                ]);
                return;
            }

            if (!empty($categoria) && strlen($categoria) > 255) {
                $this->dispatch('cerrarModalProductImport');
                $this->dispatch('mostrarErrorImportacion', [
                    'mensaje' => "Línea $linea: El nombre de la categoría excede 255 caracteres."
                ]);
                return;
            }
        }

        // Guardar productos
        foreach (array_slice($rows, 1) as $row) {
            $codigo = preg_replace('/\s+/', '', trim($row['A'] ?? ''));
            $nombre = $row['B'] ?? '';
            $descripcion = $row['C'] ?? '';
            $precio = $row['D'] ?? 0;
            $nombreCategoria = trim($row['E'] ?? '');

            $categoria = Parameter::where('codigoParametro', 'CATEGORIA')
                ->whereRaw('LOWER(nombre) = ?', [strtolower($nombreCategoria)])
                ->first();

            if (!$categoria && !empty($nombreCategoria)) {
                $nuevoIdParametro = Parameter::where('codigoParametro', 'CATEGORIA')->max('idParametro') + 1;
                $categoria = Parameter::create([
                    'idParametro' => $nuevoIdParametro,
                    'tipo' => '2',
                    'codigoParametro' => 'CATEGORIA',
                    'nombre' => $nombreCategoria,
                    'nombreCorto' => $nombreCategoria,
                    'orden' => $nuevoIdParametro,
                    'auditoriaFechaCreacion' => now(),
                    'auditoriaCreadoPor' => $userId,
                ]);
            }

            $categoriaId = $categoria->idParametro ?? null;

            $producto = Product::where('codigo', $codigo)->first();
            if ($producto) {
                $producto->update([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'categoria_id' => $categoriaId,
                    'auditoriaFechaModificacion' => now(),
                    'auditoriaModificadoPor' => $userId,
                ]);
            } else {
                Product::create([
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'categoria_id' => $categoriaId,
                    'auditoriaFechaCreacion' => now(),
                    'auditoriaCreadoPor' => $userId,
                ]);
            }
        }

        // Procesar imágenes
        $erroresImagenes = [];

        if ($this->imagenesZip instanceof UploadedFile) {
            $zipRealPath = $this->imagenesZip->getRealPath();
            $zip = new \ZipArchive;

            if ($zip->open($zipRealPath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);

                    if (in_array(strtolower(pathinfo($entryName, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'])) {
                        $codigo = preg_replace('/\s+/', '', pathinfo($entryName, PATHINFO_FILENAME));
                        $stream = $zip->getFromIndex($i);

                        if ($stream !== false) {
                            $tempPath = tempnam(sys_get_temp_dir(), 'img_');
                            file_put_contents($tempPath, $stream);

                            $producto = Product::where('codigo', $codigo)->first();

                            if ($producto) {
                                $this->procesarImagenRuta($producto, $tempPath, $userId);
                            } else {
                                $erroresImagenes[] = "No se encontró un producto con el código: $codigo";
                            }

                            unlink($tempPath);
                        }
                    }
                }
                $zip->close();
            }
        }

        $this->dispatch('actualiza-lista-producto');
        $this->dispatch('cerrarModalProductImport');

        if (!empty($erroresImagenes)) {
            $this->dispatch('mostrarErrorImportacion', [
                'mensaje' => 'Productos importados pero algunas imágenes fallaron:<br>' . implode('<br>', $erroresImagenes)
            ]);
        } else {
            $this->dispatch('mostrarExitoImportacion', [
                'mensaje' => 'Productos e imágenes importados correctamente.'
            ]);
        }
    }




    private function procesarImagenRuta($producto, $imagenRuta, $userId)
    {
        $contenido = file_get_contents($imagenRuta);
        $nombreFinal = 'productos/' . uniqid() . '.' . pathinfo($imagenRuta, PATHINFO_EXTENSION);

        Storage::disk('public')->put($nombreFinal, $contenido);

        $imagenAnterior = $producto->imagenes()->where('es_principal', true)->first();

        if ($imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior->imagen_url);
            $imagenAnterior->update([
                'imagen_url' => $nombreFinal,
                'auditoriaFechaModificacion' => now(),
                'auditoriaModificadoPor' => $userId,
            ]);
        } else {
            ProductImage::create([
                'product_id' => $producto->id,
                'imagen_url' => $nombreFinal,
                'es_principal' => true,
                'auditoriaFechaCreacion' => now(),
                'auditoriaCreadoPor' => $userId,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.products.import');
    }
}
