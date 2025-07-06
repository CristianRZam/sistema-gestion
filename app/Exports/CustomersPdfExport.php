<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Clientes';
    protected array $headings = ['Nº', 'Nombre', 'Documento', 'Teléfono', 'Correo electrónico', 'Dirección'];
    protected string $view = 'pdf.reporte-customer'; // Cambia el nombre si tienes otra vista para clientes

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    protected function generateData(): array
    {
        $query = Customer::query();

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
