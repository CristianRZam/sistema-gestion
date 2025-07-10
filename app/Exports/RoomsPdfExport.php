<?php

namespace App\Exports;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomsPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Habitaciones';
    protected array $headings = ['Nº', 'Nº Habitación', 'Tipo', 'Piso', 'Precio (S/)', 'Precio Promoción (S/)', 'Estado'];
    protected string $view = 'pdf.reporte-room';

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function generateData(): array
    {
        // Consulta base
        $query = Room::query()
            ->with(['tipo', 'piso', 'estado'])
            ->whereNull('auditoriaFechaEliminacion');

        // Filtros
        if ($this->request->filled('tipo_ids')) {
            $query->whereIn('tipo_id', $this->request->tipo_ids);
        }

        if ($this->request->filled('piso_ids')) {
            $query->whereIn('piso_id', $this->request->piso_ids);
        }

        if ($this->request->filled('estado_ids')) {
            $query->whereIn('estado_id', $this->request->estado_ids);
        }

        return $query->get()
            ->values()
            ->map(function ($habitacion, $index) {
                return [
                    'nro' => $index + 1,
                    'numero' => $habitacion->numero,
                    'tipo' => $habitacion->tipo?->nombre ?? '-',
                    'piso' => $habitacion->piso?->nombre ?? '-',
                    'precio' => number_format($habitacion->precio, 2),
                    'precio_promocion' => $habitacion->precio_promocion !== null
                        ? number_format($habitacion->precio_promocion, 2)
                        : null,
                    'estado' => $habitacion->estado?->nombre ?? '-',
                ];
            })
            ->toArray();
    }
}
