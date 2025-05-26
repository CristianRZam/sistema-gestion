<?php

namespace App\Exports;


use Spatie\Permission\Models\Role;

class RolesPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Roles';
    protected array $headings = ['Nº', 'Nombre'];
    protected string $view = 'pdf.reporte-role';

    protected function generateData(): array
    {
        return Role::select('name')
            ->orderBy('name')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    'nro' => $index + 1,
                    'nombre' => $item->name,
                ];
            })->toArray();
    }
}
