<?php

namespace App\Exports;

use App\Models\Parameter;

class ParametersPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Parámetros';
    protected array $headings = ['Nº', 'Nombre', 'Nombre Corto', 'Orden', 'Código Parametro'];
    protected string $view = 'pdf.reporte-parameter';

    protected function generateData(): array
    {
        return Parameter::whereNull('auditoriaFechaEliminacion')
            ->select('nombre', 'nombreCorto', 'orden', 'codigoParametro')
            ->orderBy('codigoParametro')
            ->orderBy('orden')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    'nro' => $index + 1,
                    'nombre' => $item->nombre,
                    'nombreCorto' => $item->nombreCorto,
                    'orden' => $item->orden,
                    'codigoParametro' => $item->codigoParametro,
                ];
            })->toArray();
    }
}
