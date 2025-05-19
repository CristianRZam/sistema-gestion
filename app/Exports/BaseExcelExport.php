<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

abstract class BaseExcelExport implements WithHeadings, ShouldAutoSize, WithEvents, WithTitle
{
    protected string $reportTitle = 'Reporte';
    protected array $headings = [];

    public function title(): string
    {
        return $this->reportTitle;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    protected function getUserName(): string
    {
        return Auth::user()?->name ?? 'Usuario desconocido';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();

                // Obtener fecha y hora en horario de Perú
                $now     = Carbon::now('America/Lima');
                $fecha   = $now->format('d/m/Y');
                $hora    = $now->format('h:i A');
                $usuario = $this->getUserName();

                //–– 1) Encabezado con espacio en blanco tras el título
                $sheet->insertNewRowBefore(1, 6);

                // Título (fila 1)
                $sheet->setCellValue('A1', $this->reportTitle);
                $sheet->mergeCells('A1:' . chr(65 + count($this->headings) - 1) . '1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Fecha (fila 3), Hora (4), Usuario (5)
                $sheet->setCellValue('B3', 'Fecha:');
                $sheet->setCellValue('C3', $fecha);
                $sheet->setCellValue('B4', 'Hora:');
                $sheet->setCellValue('C4', $hora);
                $sheet->setCellValue('B5', 'Usuario:');
                $sheet->setCellValue('C5', $usuario);

                // Negrita para etiquetas
                foreach (['B3', 'B4', 'B5'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }

                //–– 2) Cabecera de la tabla (fila 7)
                $headingRow = 7;
                $headingRange = 'A' . $headingRow . ':' . chr(65 + count($this->headings) - 1) . $headingRow;
                $sheet->getStyle($headingRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '00cbe2'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Auto tamaño ejes heredadas por ShouldAutoSize

                //–– 3) Espacio en blanco después de los datos
                $lastRow = $sheet->getHighestRow();
                $sheet->insertNewRowBefore($lastRow + 1, 1);
            },
        ];
    }
}
