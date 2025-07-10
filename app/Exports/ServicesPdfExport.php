<?php

namespace App\Exports;

use App\Models\Service;

class ServicesPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Servicios';
    protected array $headings = ['Nº', 'Nombre', 'Precio (S/)', 'Estado'];
    protected string $view = 'pdf.reporte-service';

    protected function generateData(): array
    {
        return Service::whereNull('auditoriaFechaEliminacion')
            ->orderBy('nombre')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    'nro'     => $index + 1,
                    'nombre'  => $item->nombre,
                    'precio'  => number_format($item->precio, 2),
                    'estado'  => $item->activo ? 'Habilitado' : 'Inhabilitado',
                ];
            })->toArray();
    }
}
