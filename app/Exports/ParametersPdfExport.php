<?php

namespace App\Exports;

use App\Models\Parameter;
use Illuminate\Http\Request;

class ParametersPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Parámetros';
    protected array $headings = ['Nº', 'Nombre', 'Nombre Corto', 'Orden', 'Código Parametro'];
    protected string $view = 'pdf.reporte-parameter';

    protected Request $request;
    public function __construct(Request $request){
        $this->request = $request;
    }

    protected function generateData(): array
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
                    'nro' => $index + 1,
                    'nombre' => $item->nombre,
                    'nombreCorto' => $item->nombreCorto,
                    'orden' => $item->orden,
                    'codigoParametro' => $item->codigoParametro,
                ];
            })->toArray();
    }
}
