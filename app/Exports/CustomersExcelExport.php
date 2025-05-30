<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class CustomersExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Clientes';
    protected array $headings = ['Nº', 'Nombre', 'Documento', 'Teléfono', 'Correo electrónico', 'Dirección'];

    public function collection(): Collection
    {
        return Customer::whereNull('auditoriaFechaEliminacion')
            ->orderBy('nombre')
            ->select('nombre', 'documento', 'telefono', 'email', 'direccion')
            ->get()
            ->values() // Reindexar para enumerar correctamente
            ->map(function ($item, $index) {
                return [
                    $index + 1,          // Nº
                    $item->nombre,
                    $item->documento,
                    $item->telefono,
                    $item->email,
                    $item->direccion,
                ];
            });
    }
}
