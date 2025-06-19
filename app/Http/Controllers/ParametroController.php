<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParametroController extends Controller
{
    public function descargar($codigoParametro)
    {
        // Buscar el parámetro por su código
        $parametro = Parameter::where('codigoParametro', $codigoParametro)->first();

        // Verificar existencia del parámetro y del archivo
        if (!$parametro || !Storage::disk('public')->exists($parametro->nombre)) {
            return response()->json(['mensaje' => 'Archivo no encontrado.'], 404);
        }

        // Obtener extensión del archivo original
        $extension = pathinfo($parametro->nombre, PATHINFO_EXTENSION);

        // Usar nombreCorto si existe, con la extensión original
        $nombreDescarga = $parametro->nombreCorto
            ? "{$parametro->nombreCorto}.{$extension}"
            : basename($parametro->nombre);

        // Descargar el archivo
        return Storage::disk('public')->download($parametro->nombre, $nombreDescarga);
    }
}
