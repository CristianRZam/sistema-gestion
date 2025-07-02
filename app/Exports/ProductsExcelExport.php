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
    protected array $headings = ['Nº', 'Código', 'Nombre', 'Descripción', 'Stock', 'Precio (S/)', 'Categoría'];

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
        $query = Product::query();

        // Filtros opcionales
        if ($this->request->filled('categoria_id')) {
            $query->where('categoria_id', $this->request->categoria_id);
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
                $index + 1, // Nº
                $producto->codigo,
                $producto->nombre,
                $producto->descripcion,
                $producto->stock,
                number_format($producto->precio, 2), // Precio (S/)
                $categorias[$producto->categoria_id] ?? '-', // Categoría lógica
            ];
        });
    }
}
