<?php

namespace App\Exports;

use App\Models\Sale;

class SalesPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Ventas';
    protected array $headings = ['Nº', 'Fecha', 'Cliente', 'Vendedor', 'Total'];
    protected string $view = 'pdf.reporte-sale';

    protected function generateData(): array
    {
        return Sale::with(['customer', 'vendedor'])
            ->get()
            ->values()
            ->map(function ($sale, $index) {
                return [
                    'nro' => $index + 1,
                    'fecha' => optional($sale->fecha_venta)->format('d/m/Y H:i'),
                    'cliente' => optional($sale->customer)->nombre ?? '-',
                    'vendedor' => optional($sale->vendedor)->name ?? '-',
                    'total' => number_format($sale->total, 2),
                ];
            })->toArray();
    }
}
