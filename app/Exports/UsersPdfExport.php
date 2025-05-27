<?php

namespace App\Exports;



use App\Models\User;

class UsersPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Usuarios';
    protected array $headings = ['Nº', 'Nombre','Correo Electrónico', 'Perfil'];
    protected string $view = 'pdf.reporte-user';

    protected function generateData(): array
    {
        return User::with('roles')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    'nro' => $index + 1,
                    'nombre' => $item->name,
                    'correo' => $item->email,
                    'perfil' => $item->roles->first()?->name ?? '',
                ];
            })->toArray();
    }
}
