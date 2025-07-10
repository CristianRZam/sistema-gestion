<?php

namespace App\Exports;

use App\Models\Parameter;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class RoomsExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Habitaciones';
    protected array $headings = ['Nº', 'Nº Habitación', 'Tipo', 'Piso', 'Precio (S/)', 'Precio Promoción (S/)', 'Estado',];

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection(): Collection
    {

        // Construir consulta base
        $query = Room::query()->with(['tipo', 'piso', 'estado']);

        // Filtros opcionales
        if ($this->request->filled('tipo_ids')) {
            $query->whereIn('tipo_id', $this->request->tipo_ids);
        }

        if ($this->request->filled('piso_ids')) {
            $query->whereIn('piso_id', $this->request->piso_ids);
        }

        if ($this->request->filled('estado_ids')) {
            $query->whereIn('estado_id', $this->request->estado_ids);
        }


        // Construir colección para exportar
        return $query->get()->values()->map(function ($habitacion, $index) {
            return [
                $index + 1,
                $habitacion->numero,
                $habitacion->tipo?->nombre ?? '-',
                $habitacion->piso?->nombre ?? '-',
                number_format($habitacion->precio, 2),
                $habitacion->precio_promocion !== null
                    ? number_format($habitacion->precio_promocion, 2)
                    : '',
                $habitacion->estado?->nombre ?? '-',
            ];
        });
    }
}
