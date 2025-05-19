<?php

namespace App\Exports;

use App\Models\Parameter;

class CategoriesPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Categorías';
    protected array $headings = ['Nº', 'Nombre', 'Nombre Corto', 'Orden'];
    protected string $view = 'pdf.reporte-category';

    protected function generateData(): array
    {
        return Parameter::where('tipo', 'CATEGORIA')
            ->whereNull('auditoriaFechaEliminacion')
            ->select('nombre', 'nombreCorto', 'orden')
            ->orderBy('orden')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    'nro' => $index + 1,
                    'nombre' => $item->nombre,
                    'nombreCorto' => $item->nombreCorto,
                    'orden' => $item->orden,
                ];
            })->toArray();
    }
}
