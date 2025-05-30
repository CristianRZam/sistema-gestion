<?php

namespace App\Exports;

use App\Models\Customer;

class CustomersPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Clientes';
    protected array $headings = ['Nº', 'Nombre', 'Documento', 'Teléfono', 'Correo electrónico', 'Dirección'];
    protected string $view = 'pdf.reporte-customer'; // Cambia el nombre si tienes otra vista para clientes

    protected function generateData(): array
    {
        return Customer::whereNull('auditoriaFechaEliminacion')
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
