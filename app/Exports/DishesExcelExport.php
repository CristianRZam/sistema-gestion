<?php

namespace App\Exports;

use App\Models\Dish;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class DishesExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Platillos';
    protected array $headings = ['Nº', 'Código', 'Nombre', 'Descripción', 'Precio (S/)', 'Precio Promoción (S/)', 'Categoría', 'Estado'];

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection(): Collection
    {
        // Obtener todas las categorías de platillos indexadas por idParametro
        $categorias = Parameter::where('codigoParametro', 'CATEGORIA_PLATILLO')
            ->pluck('nombre', 'idParametro');

        // Construir consulta base
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

        // Construir colección para exportar
        return $query->get()->values()->map(function ($platillo, $index) use ($categorias) {
            return [
                $index + 1,                                      // Nº
                $platillo->codigo,                               // Código
                $platillo->nombre,                               // Nombre
                $platillo->descripcion,                          // Descripción
                number_format($platillo->precio, 2),             // Precio (S/)
                $platillo->precio_promocion !== null             // Precio Promoción (S/)
                    ? number_format($platillo->precio_promocion, 2)
                    : '',
                $categorias[$platillo->categoria_id] ?? '-',     // Categoría
                $platillo->activo ? 'Disponible' : 'No Disponible',                 // Activo
            ];
        });
    }
}
