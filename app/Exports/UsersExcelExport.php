<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Usuarios';
    protected array $headings = ['Nº', 'Nombre', 'Correo Electrónico', 'Perfil'];
    public function collection(): Collection
    {
        return User::with('roles')
        ->get()
            ->values()
            ->map(function ($user, $index) {
                return [
                    $index + 1,
                    $user->name,
                    $user->email,
                    $user->roles->first()?->name ?? '',
                ];
            });
    }
}
