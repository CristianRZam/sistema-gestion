<?php

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SuppliersPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Proveedores';
    protected array $headings = ['Nº', 'Nombre', 'Documento', 'Teléfono', 'Correo electrónico', 'Dirección'];
    protected string $view = 'pdf.reporte-supplier'; // Cambia el nombre según tu vista

    protected Request $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    protected function generateData(): array
    {
        $query = Supplier::query()->whereNull('auditoriaFechaEliminacion');

        // Filtro por nombre
        if ($this->request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $this->request->nombre . '%');
        }

        // Filtro por documento
        if ($this->request->filled('numero_documento')) {
            $query->where('documento', 'like', '%' . $this->request->numero_documento . '%');
        }

        return $query->get()->values()->map(function ($item, $index) {
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
