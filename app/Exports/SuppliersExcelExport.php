<?php

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class SuppliersExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Proveedores';
    protected array $headings = ['Nº', 'Nombre', 'Documento', 'Teléfono', 'Correo electrónico', 'Dirección'];

    public function collection(): Collection
    {
        return Supplier::whereNull('auditoriaFechaEliminacion')
            ->orderBy('nombre')
            ->select('nombre', 'documento', 'telefono', 'email', 'direccion')
            ->get()
            ->values() // Asegura que los índices comiencen desde 0
            ->map(function ($item, $index) {
                return [
                    $index + 1,         // Nº
                    $item->nombre,
                    $item->documento,
                    $item->telefono,
                    $item->email,
                    $item->direccion,
                ];
            });
    }
}
