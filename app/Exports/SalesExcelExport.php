<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExcelExport extends BaseExcelExport implements FromCollection, WithHeadings, WithMapping
{
    protected string $reportTitle = 'Reporte de Ventas';
    protected array $headings = ['Nº', 'Fecha', 'Cliente', 'Vendedor', 'Total'];

    public function collection()
    {
        return Sale::with(['customer', 'vendedor'])->get();
    }

    public function map($sale): array
    {
        static $rowNumber = 1;

        return [
            $rowNumber++, // Número correlativo
            optional($sale->fecha_venta)->format('d/m/Y H:i'), // Fecha
            optional($sale->customer)->nombre ?? '-', // Cliente
            optional($sale->vendedor)->name ?? '-', // Vendedor
            number_format($sale->total, 2), // Total
        ];
    }
}
