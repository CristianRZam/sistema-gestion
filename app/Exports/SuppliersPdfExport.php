<?php

namespace App\Exports;

use App\Models\Supplier;

class SuppliersPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Proveedores';
    protected array $headings = ['Nº', 'Nombre', 'Documento', 'Teléfono', 'Correo electrónico', 'Dirección'];
    protected string $view = 'pdf.reporte-supplier'; // Cambia el nombre según tu vista

    protected function generateData(): array
    {
        return Supplier::whereNull('auditoriaFechaEliminacion')
            ->orderBy('nombre')
            ->select('nombre', 'documento', 'telefono', 'email', 'direccion')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    'nro' => $index + 1,
                    'nombre' => $item->nombre,
                    'documento' => $item->documento,
                    'telefono' => $item->telefono,
                    'email' => $item->email,
                    'direccion' => $item->direccion,
                ];
            })->toArray();
    }
}
