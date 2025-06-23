<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class ProductController extends Controller
{
    public function descargarImagenes(Request $request)
    {
        $query = Product::query()
            ->whereNull('auditoriaFechaEliminacion')
            ->with(['imagenes' => function ($q) {
                $q->where('es_principal', true);
            }]);

        if ($request->nombre) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        if ($request->categoria_id) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if (is_numeric($request->stock) && $request->stock > 0) {
            $query->where('stock', '>=', $request->stock);
        }

        $productos = $query->get();

        // Filtra solo productos que tienen imagen principal válida
        $productosConImagen = $productos->filter(function ($producto) {
            $img = $producto->imagenes->first();
            return $img && \Storage::disk('public')->exists($img->imagen_url);
        });

        if ($productosConImagen->isEmpty()) {
            return back()->with('error', 'No hay productos con imágenes válidas para descargar.');
        }

        $zip = new ZipArchive;
        $zipFileName = 'imagenes_productos_' . now()->timestamp . '.zip';
        $zipPath = storage_path("app/public/{$zipFileName}");

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($productosConImagen as $producto) {
                $img = $producto->imagenes->first();
                $imagenPath = storage_path("app/public/" . $img->imagen_url);
                $nombreArchivo = $producto->codigo . '.' . pathinfo($imagenPath, PATHINFO_EXTENSION);

                $zip->addFile($imagenPath, $nombreArchivo);
            }
            $zip->close();
        } else {
            return back()->with('error', 'No se pudo crear el archivo ZIP.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

}
