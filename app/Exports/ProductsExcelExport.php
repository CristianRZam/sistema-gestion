<?php

namespace App\Exports;

use App\Models\Parameter;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductsExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Productos';
    protected array $headings = ['Nº', 'Código', 'Nombre', 'Descripción', 'Stock', 'Precio (S/)', 'Precio Promoción (S/)', 'Categoría'];

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection(): Collection
    {
        // Obtener todas las categorías indexadas por idParametro
        $categorias = Parameter::where('codigoParametro', 'CATEGORIA')
            ->pluck('nombre', 'idParametro');

        // Construir consulta base
        $query = Product::query()->whereNull('auditoriaFechaEliminacion');

        // Filtros opcionales
        if ($this->request->filled('product_ids')) {
            $ids = is_array($this->request->product_ids)
                ? $this->request->product_ids
                : explode(',', $this->request->product_ids);
            $query->whereIn('id', $ids);
        }


        if ($this->request->filled('categoria_ids')) {
            $query->whereIn('categoria_id', $this->request->categoria_ids);
        }

        if ($this->request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $this->request->nombre . '%');
        }

        if ($this->request->filled('stock')) {
            $query->where('stock', '>=', $this->request->stock);
        }

        // Construir colección para exportar
        return $query->get()->values()->map(function ($producto, $index) use ($categorias) {
            return [
                $index + 1,                               // Nº
                $producto->codigo,                        // Código
                $producto->nombre,                        // Nombre
                $producto->descripcion,                   // Descripción
                $producto->stock,                         // Stock
                number_format($producto->precio, 2),      // Precio (S/)
                $producto->precio_promocion !== null      // Precio Promoción
                    ? number_format($producto->precio_promocion, 2)
                    : '',
                $categorias[$producto->categoria_id] ?? '-', // Categoría lógica
            ];
        });
    }
}
