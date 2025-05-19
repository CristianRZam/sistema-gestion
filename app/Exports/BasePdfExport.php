<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

abstract class BasePdfExport
{
    protected string $reportTitle = 'Reporte';
    protected array $headings = [];
    protected string $view = ''; // Blade view
    protected array $data = [];

    abstract protected function generateData(): array;

    public function download(string $filename)
    {
        $this->data = $this->generateData();

        $pdf = Pdf::loadView($this->view, [
            'title' => $this->reportTitle,
            'headings' => $this->headings,
            'rows' => $this->data,
            'fecha' => Carbon::now('America/Lima')->format('d/m/Y'),
            'hora' => Carbon::now('America/Lima')->format('h:i A'),
            'usuario' => Auth::user()?->name ?? 'Usuario desconocido',
        ]);

        return $pdf->download($filename);
    }
}
