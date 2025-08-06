<?php

namespace App\Exports;

use App\Models\Dish;
use App\Models\Parameter;
use Illuminate\Http\Request;

class DishesPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Platillos';
    protected array $headings = ['Nº', 'Código', 'Nombre', 'Descripción', 'Precio (S/)', 'Precio Promoción (S/)', 'Categoría', 'Estado'];
    protected string $view = 'pdf.reporte-dish';

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function generateData(): array
    {
        $categorias = Parameter::where('codigoParametro', 'CATEGORIA_PLATILLO')
            ->whereNull('auditoriaFechaEliminacion')
            ->pluck('nombre', 'idParametro');

        $query = Dish::query()->whereNull('auditoriaFechaEliminacion');

        // Filtros opcionales
        if ($this->request->filled('dish_ids')) {
            $ids = is_array($this->request->dish_ids)
                ? $this->request->dish_ids
                : explode(',', $this->request->dish_ids);
            $query->whereIn('id', $ids);
        }

        if ($this->request->filled('categoria_ids')) {
            $query->whereIn('categoria_id', $this->request->categoria_ids);
        }

        if ($this->request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $this->request->nombre . '%');
        }

        if ($this->request->filled('estado')) {
            $query->where('activo', $this->request->estado);
        }

        return $query->get()
            ->values()
            ->map(function ($platillo, $index) use ($categorias) {
                return [
                    'nro' => $index + 1,
                    'codigo' => $platillo->codigo,
                    'nombre' => $platillo->nombre,
                    'descripcion' => $platillo->descripcion,
                    'precio' => number_format($platillo->precio, 2),
                    'precio_promocion' => $platillo->precio_promocion !== null
                        ? number_format($platillo->precio_promocion, 2)
                        : '',
                    'categoria' => $categorias[$platillo->categoria_id] ?? '-',
                    'estado' => $platillo->activo ? 'Disponible' : 'No Disponible',
                ];
            })
            ->toArray();
    }
}
