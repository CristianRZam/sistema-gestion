<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalesExcelExport extends BaseExcelExport implements FromCollection, WithHeadings, WithMapping
{
    protected string $reportTitle = 'Reporte de Ventas';
    protected array $headings = ['Nº', 'Fecha', 'Cliente', 'Vendedor', 'Total'];

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
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

        // Filtro por estados
        if ($this->request->filled('estado_ids')) {
            $estadoIds = is_array($this->request->estado_ids)
                ? $this->request->estado_ids
                : explode(',', $this->request->estado_ids);

            $query->whereIn('estado_venta_id', $estadoIds);
        }

        // Filtro por usuarios
        if ($this->request->filled('usuario_ids')) {
            $usuarioIds = is_array($this->request->usuario_ids)
                ? $this->request->usuario_ids
                : explode(',', $this->request->usuario_ids);

            $query->whereIn('usuario_id', $usuarioIds);
        }

        return $query->get();
    }

    public function map($sale): array
    {
        static $rowNumber = 1;

        return [
            $rowNumber++,
            optional($sale->fecha_venta)->format('d/m/Y H:i'),
            optional($sale->customer)->nombre ?? '-',
            optional($sale->vendedor)->name ?? '-',
            number_format($sale->total - $sale->descuento, 2),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $now     = now('America/Lima');
                $fecha   = $now->format('d/m/Y');
                $hora    = $now->format('h:i A');
                $usuario = auth()->user()?->name ?? 'Usuario desconocido';

                $sheet->insertNewRowBefore(1, 6);

                // Título
                $sheet->setCellValue('A1', $this->reportTitle);
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Información adicional
                $sheet->setCellValue('B3', 'Fecha:');
                $sheet->setCellValue('C3', $fecha);
                $sheet->setCellValue('B4', 'Hora:');
                $sheet->setCellValue('C4', $hora);
                $sheet->setCellValue('B5', 'Usuario:');
                $sheet->setCellValue('C5', $usuario);

                foreach (['B3', 'B4', 'B5'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }

                // Cabecera
                $headingRow = 7;
                $headingRange = 'A' . $headingRow . ':E' . $headingRow;
                $sheet->getStyle($headingRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '00cbe2'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Total general
                $ventas = $this->collection();
                $total = $ventas->sum(fn($v) => $v->total - $v->descuento);

                $lastRow = $sheet->getHighestRow() + 1;
                $sheet->setCellValue("D{$lastRow}", 'Total General:');
                $sheet->setCellValue("E{$lastRow}", number_format($total, 2));
                $sheet->getStyle("D{$lastRow}:E{$lastRow}")->getFont()->setBold(true);
                $sheet->getStyle("D{$lastRow}")->getAlignment()->setHorizontal('right');
            },
        ];
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
