<?php

namespace App\Exports;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SalesPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Ventas';
    protected array $headings = ['Nº', 'Fecha', 'Cliente', 'Vendedor', 'Total'];
    protected string $view = 'pdf.reporte-sale';

    protected float $totalGeneral = 0;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function generateData(): array
    {
        $query = Sale::with(['customer', 'vendedor']);

        // Filtro por fechas (desde y hasta)
        $desde = $this->request->input('fecha_desde');
        $hasta = $this->request->input('fecha_hasta');

        if ($desde && $hasta) {
            $query->whereBetween('fecha_venta', [
                $desde . ' 00:00:00',
                $hasta . ' 23:59:59'
            ]);
        }

        // Filtro por estado(s)
        if ($this->request->filled('estado_ids')) {
            $estadoIds = is_array($this->request->estado_ids)
                ? $this->request->estado_ids
                : explode(',', $this->request->estado_ids);

            $query->whereIn('estado_venta_id', $estadoIds);
        }

        // Filtro por usuario(s)
        if ($this->request->filled('usuario_ids')) {
            $usuarioIds = is_array($this->request->usuario_ids)
                ? $this->request->usuario_ids
                : explode(',', $this->request->usuario_ids);

            $query->whereIn('usuario_id', $usuarioIds);
        }

        $ventas = $query->get();

        $this->totalGeneral = $ventas->sum(fn ($sale) => $sale->total - $sale->descuento);

        return $ventas
            ->values()
            ->map(function ($sale, $index) {
                return [
                    'nro' => $index + 1,
                    'fecha' => optional($sale->fecha_venta)->format('d/m/Y H:i'),
                    'cliente' => optional($sale->customer)->nombre ?? '-',
                    'vendedor' => optional($sale->vendedor)->name ?? '-',
                    'total' => number_format($sale->total - $sale->descuento, 2),
                ];
            })->toArray();
    }

    public function download(string $filename)
    {
        $this->data = $this->generateData();

        $pdf = Pdf::loadView($this->view, [
            'title' => $this->reportTitle,
            'headings' => $this->headings,
            'rows' => $this->data,
            'fecha' => now('America/Lima')->format('d/m/Y'),
            'hora' => now('America/Lima')->format('h:i A'),
            'usuario' => auth()->user()?->name ?? 'Usuario desconocido',
            'totalGeneral' => number_format($this->totalGeneral, 2),
        ]);

        return $pdf->download($filename);
    }
}
