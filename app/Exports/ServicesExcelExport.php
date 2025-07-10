<?php

namespace App\Exports;

use App\Models\Service;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServicesExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Servicios';
    protected array $headings = ['Nº', 'Nombre', 'Precio', 'Estado'];

    public function collection(): Collection
    {
        return Service::select('nombre', 'precio', 'activo')
            ->orderBy('nombre')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    $index + 1,                              // Nº
                    $item->nombre,                           // Nombre del servicio
                    number_format($item->precio, 2),        // Precio formateado
                    $item->activo ? 'Habilitado' : 'Inhabilitado',  // Estado
                ];
            });
    }
}
