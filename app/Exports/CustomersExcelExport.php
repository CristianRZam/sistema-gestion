<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class CustomersExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Clientes';
    protected array $headings = ['Nº', 'Nombre', 'Documento', 'Teléfono', 'Correo electrónico', 'Dirección'];

    protected Request $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function collection(): Collection
    {
        $query = Customer::query()->whereNull('auditoriaFechaEliminacion');

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
                    $index + 1,          // Nº
                    $item->nombre,
                    $item->documento,
                    $item->telefono,
                    $item->email,
                    $item->direccion,
                ];
            });
    }
}
