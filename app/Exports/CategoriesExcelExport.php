<?php

namespace App\Exports;

use App\Models\Parameter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class CategoriesExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Categorías';
    protected array $headings = ['Nº', 'Nombre', 'Nombre Corto', 'Orden'];

    public function collection(): Collection
    {
        return Parameter::where('tipo', 'CATEGORIA')
            ->whereNull('auditoriaFechaEliminacion')
            ->select('nombre', 'nombreCorto', 'orden')
            ->orderBy('orden')
            ->get()
            ->values() // Asegura que el índice sea 0, 1, 2,...
            ->map(function ($item, $index) {
                return [
                    $index + 1,            // Nº
                    $item->nombre,
                    $item->nombreCorto,
                    $item->orden,
                ];
            });
    }
}
