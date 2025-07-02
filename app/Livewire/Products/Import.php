<?php

namespace App\Livewire\Products;

use App\Models\Parameter;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;

class Import extends Component
{
    use WithFileUploads;

    public $excelFile;
    public $imagenes = [];

    protected $listeners = ['open-modal-product-import' => 'resetearCampos'];

    public function resetearCampos()
    {
        $this->reset(['excelFile', 'imagenes']);
        $this->resetValidation();

        $this->dispatch('abrirModalProductImport');
    }


    public function guardarProducto()
    {
        $this->validate([
            'excelFile' => 'nullable|file|mimes:xlsx,xls',
            'imagenes.*' => 'nullable|file|image|max:2048',
        ]);

        if (!$this->excelFile && empty($this->imagenes)) {
            $this->addError('excelFile', 'Debe subir el archivo Excel o la carpeta de imagenes.');
            $this->addError('imagenes', 'Debe subir el archivo Excel o la carpeta de imagenes.');
            return;
        }

        $userId = auth()->id();
        $rows = [];

        if ($this->excelFile instanceof UploadedFile) {
            $spreadsheet = IOFactory::load($this->excelFile->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        }

        // Mapear imágenes subidas con el nombre del archivo sin extensión
        $imagenesMap = [];
        foreach ($this->imagenes as $imagen) {
            $nombreArchivo = pathinfo($imagen->getClientOriginalName(), PATHINFO_FILENAME); // Sin extensión
            $imagenesMap[$nombreArchivo] = $imagen;
        }

        $erroresImagenes = [];

        // ✅ Caso 1: Hay Excel -> procesa filas
        if (!empty($rows)) {
            foreach (array_slice($rows, 1) as $row) {
                $codigo = trim($row['A'] ?? '');
                if (empty($codigo)) continue;

                $nombre = $row['B'] ?? '';
                $descripcion = $row['C'] ?? '';
                $precio = $row['D'] ?? 0;
                $stock = $row['E'] ?? 0;
                $nombreCategoria = trim($row['F'] ?? '');

                // Obtener o registrar el idParametro correspondiente al nombre de categoría
                $categoria = Parameter::where('codigoParametro', 'CATEGORIA')
                    ->whereRaw('LOWER(nombre) = ?', [strtolower($nombreCategoria)])
                    ->first();

                if (!$categoria && !empty($nombreCategoria)) {
                    $nuevoIdParametro = Parameter::where('codigoParametro', 'CATEGORIA')->max('idParametro') + 1;

                    $categoria = Parameter::create([
                        'idParametro' => $nuevoIdParametro,
                        'tipo' => '2', // Asumiendo tipo texto. Cambia si tu sistema usa otro tipo.
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
                        'stock' => $stock,
                        'categoria_id' => $categoriaId,
                        'auditoriaFechaModificacion' => now(),
                        'auditoriaModificadoPor' => $userId,
                    ]);
                } else {
                    $producto = Product::create([
                        'codigo' => $codigo,
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'precio' => $precio,
                        'stock' => $stock,
                        'categoria_id' => $categoriaId,
                        'auditoriaFechaCreacion' => now(),
                        'auditoriaCreadoPor' => $userId,
                    ]);
                }

                // Procesar imagen
                if (isset($imagenesMap[$codigo])) {
                    $this->procesarImagenProducto($producto, $imagenesMap[$codigo], $userId);
                    unset($imagenesMap[$codigo]);
                }
            }
        }

        // ✅ Caso 2: No hay Excel, solo imágenes
        if (empty($rows) && !empty($imagenesMap)) {
            foreach ($imagenesMap as $codigo => $imagen) {
                $producto = Product::where('codigo', $codigo)->first();
                if ($producto) {
                    $this->procesarImagenProducto($producto, $imagen, $userId);
                } else {
                    $erroresImagenes[] = "No se encontró un producto con el código: $codigo";
                }
            }
        }

        $this->dispatch('cerrarModalProductImport');
        $this->dispatch('actualiza-lista-producto');

        if (!empty($erroresImagenes)) {
            session()->flash('message', 'Productos e imágenes importados con algunos errores: ' . implode(', ', $erroresImagenes));
        } else {
            session()->flash('message', 'Productos e imágenes importados correctamente.');
        }
    }

    private function procesarImagenProducto($producto, $imagen, $userId)
    {
        $rutaNueva = $imagen->store("productos", "public");

        $imagenAnterior = $producto->imagenes()->where('es_principal', true)->first();

        if ($imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior->imagen_url);
            $imagenAnterior->update([
                'imagen_url' => $rutaNueva,
                'auditoriaFechaModificacion' => now(),
                'auditoriaModificadoPor' => $userId,
            ]);
        } else {
            ProductImage::create([
                'product_id' => $producto->id,
                'imagen_url' => $rutaNueva,
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
