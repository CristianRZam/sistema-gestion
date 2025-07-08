<?php

namespace App\Exports;

use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchasesPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Compras';
    protected array $headings = ['Nº', 'Fecha', 'Proveedor', 'Comprador', 'Total'];
    protected string $view = 'pdf.reporte-purchase'; // Asegúrate de tener esta vista

    protected float $totalGeneral = 0;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function generateData(): array
    {
        $query = Purchase::with(['supplier', 'comprador']);

        // Filtro por fechas (desde y hasta)
        $desde = $this->request->input('fecha_desde');
        $hasta = $this->request->input('fecha_hasta');

        if ($desde && $hasta) {
            $query->whereBetween('fecha_compra', [
                $desde . ' 00:00:00',
                $hasta . ' 23:59:59'
            ]);
        }

        // Filtro por estado(s)
        if ($this->request->filled('estado_ids')) {
            $estadoIds = is_array($this->request->estado_ids)
                ? $this->request->estado_ids
                : explode(',', $this->request->estado_ids);

            $query->whereIn('estado_compra_id', $estadoIds);
        }

        // Filtro por usuario(s)
        if ($this->request->filled('usuario_ids')) {
            $usuarioIds = is_array($this->request->usuario_ids)
                ? $this->request->usuario_ids
                : explode(',', $this->request->usuario_ids);

            $query->whereIn('usuario_id', $usuarioIds);
        }

        $compras = $query->get();

        $this->totalGeneral = $compras->sum(fn($compra) => $compra->total);

        return $compras
            ->values()
            ->map(function ($compra, $index) {
                return [
                    'nro' => $index + 1,
                    'fecha' => optional($compra->fecha_compra)->format('d/m/Y H:i'),
                    'proveedor' => optional($compra->supplier)->nombre ?? '-',
                    'comprador' => optional($compra->comprador)->name ?? '-',
                    'total' => number_format($compra->total, 2),
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
