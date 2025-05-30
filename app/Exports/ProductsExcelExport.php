<?php

namespace App\Exports;

use App\Models\Parameter;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductsExcelExport extends BaseExcelExport implements FromCollection
{
    protected string $reportTitle = 'Reporte de Productos';
    protected array $headings = ['Nº', 'Nombre', 'Descripción', 'Stock', 'Precio (S/)', 'Categoría'];
    public function collection(): Collection
    {
        // Obtener todas las categorías (tipo CATEGORIA) indexadas por idParametro
        $categorias = Parameter::where('tipo', 'CATEGORIA')
            ->pluck('nombre', 'idParametro');

        // Obtener todos los productos
        return Product::all()
            ->values()
            ->map(function ($producto, $index) use ($categorias) {
                return [
                    $index + 1,                               // Nº
                    $producto->nombre,
                    $producto->descripcion,
                    $producto->stock,
                    number_format($producto->precio, 2),     // Precio (S/)
                    $categorias[$producto->categoria_id] ?? '-', // Nombre de categoría lógica
                ];
            });
    }
}
