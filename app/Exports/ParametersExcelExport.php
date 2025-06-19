<?php

namespace App\Exports;

use App\Models\Parameter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ParametersExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Parámetros';
    protected array $headings = ['Nº', 'Nombre', 'Nombre Corto', 'Orden', 'Código Parametro'];

    public function collection(): Collection
    {
        return Parameter::whereNull('auditoriaFechaEliminacion')
            ->select('nombre', 'nombreCorto', 'orden', 'codigoParametro')
            ->orderBy('codigoParametro')
            ->orderBy('orden')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    $index + 1,            // Nº
                    $item->nombre,
                    $item->nombreCorto,
                    $item->orden,
                    $item->codigoParametro,
                ];
            });
    }
}
