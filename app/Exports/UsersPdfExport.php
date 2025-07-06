<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;

class UsersPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Usuarios';
    protected array $headings = ['Nº', 'Nombre', 'Correo Electrónico', 'Rol', 'Estado'];
    protected string $view = 'pdf.reporte-user';

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function generateData(): array
    {
        $query = User::with('roles');

        // Filtro por nombre
        if ($this->request->filled('nombre')) {
            $query->where('name', 'like', '%' . $this->request->nombre . '%');
        }

        // Filtro por estado (activo o inactivo)
        if ($this->request->has('activo')) {
            $estado = filter_var($this->request->activo, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($estado)) {
                $query->where('activo', $estado);
            }
        }

        // Filtro por roles (IDs)
        if ($this->request->filled('rol_ids')) {
            $ids = is_array($this->request->rol_ids)
                ? $this->request->rol_ids
                : explode(',', $this->request->rol_ids);

            $query->whereHas('roles', function ($q) use ($ids) {
                $q->whereIn('id', $ids);
            });
        }

        return $query->get()->values()->map(function ($item, $index) {
            return [
                'nro' => $index + 1,
                'nombre' => $item->name,
                'correo' => $item->email,
                'perfil' => $item->roles->first()?->name ?? 'Sin rol',
                'estado' => $item->activo ? 'Habilitado' : 'Deshabilitado',
            ];
        })->toArray();
    }
}
