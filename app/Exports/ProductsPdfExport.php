<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Parameter;

class ProductsPdfExport extends BasePdfExport
{
    protected string $reportTitle = 'Reporte de Productos';
    protected array $headings = ['Nº', 'Nombre', 'Descripción', 'Stock', 'Precio (S/)', 'Categoría'];
    protected string $view = 'pdf.reporte-product';

    protected function generateData(): array
    {
        $categorias = Parameter::where('tipo', 'CATEGORIA')
            ->whereNull('auditoriaFechaEliminacion')
            ->pluck('nombre', 'idParametro');

        return Product::all()
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
            })->toArray();
    }
}
