<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Parameter;
use Illuminate\Http\Request;

class ProductsPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Productos';
    protected array $headings = ['Nº', 'Nombre', 'Descripción', 'Stock', 'Precio (S/)', 'Categoría'];
    protected string $view = 'pdf.reporte-product';

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function generateData(): array
    {
        $categorias = Parameter::where('codigoParametro', 'CATEGORIA')
            ->whereNull('auditoriaFechaEliminacion')
            ->pluck('nombre', 'idParametro');

        // Construir consulta base
        $query = Product::query();

        // Aplicar filtros si existen
        if ($this->request->filled('categoria_id')) {
            $query->where('categoria_id', $this->request->categoria_id);
        }

        if ($this->request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $this->request->nombre . '%');
        }

        if ($this->request->filled('stock')) {
            $query->where('stock', '>=', $this->request->stock);
        }

        return $query->get()
            ->values()
            ->map(function ($producto, $index) use ($categorias) {
                return [
                    'nro' => $index + 1,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'stock' => $producto->stock,
                    'precio' => number_format($producto->precio, 2),
                    'categoria' => $categorias[$producto->categoria_id] ?? '-',
                ];
            })
            ->toArray();
    }
}
