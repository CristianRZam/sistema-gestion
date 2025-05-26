<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Spatie\Permission\Models\Role;

class RolesExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Roles';
    protected array $headings = ['Nº', 'Nombre'];

    public function collection(): Collection
    {
        return Role::select('name')
            ->get()
            ->values() // Asegura que el índice sea 0, 1, 2,...
            ->map(function ($item, $index) {
                return [
                    $index + 1,            // Nº
                    $item->name,
                ];
            });
    }
}
