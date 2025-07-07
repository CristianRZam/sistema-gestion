<?php

namespace App\Exports;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ParametersExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Parámetros';
    protected array $headings = ['Nº', 'Nombre', 'Nombre Corto', 'Orden', 'Código Parametro'];

    protected Request $request;
    public function __construct(Request $request){
        $this->request = $request;
    }
    public function collection(): Collection
    {
        $query = Parameter::query()->whereNull('auditoriaFechaEliminacion');

        // Aplicar filtros si existen
        if ($this->request->filled('tipo_ids')) {
            $query->whereIn('tipo', $this->request->tipo_ids);
        }

        if ($this->request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $this->request->nombre . '%');
        }

        if ($this->request->filled('codigo')) {
            $query->where('codigoParametro', 'like', '%' . $this->request->codigo . '%');
        }


        return $query->get()->values()->map(function ($item, $index) {
                return [
                    $index + 1,            // Nº
                    $item->nombre,
                    $item->nombreCorto,
                    $item->orden,
                    $item->codigoParametro,
                ];
            });
    }
}
