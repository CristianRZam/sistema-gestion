<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Parameter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ProductsCatalogPdfExport
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download()
    {
        // Obtener categorías
        $categorias = Parameter::where('codigoParametro', 'CATEGORIA')
            ->whereNull('auditoriaFechaEliminacion')
            ->pluck('nombre', 'idParametro');

        // Obtener imagen de encabezado
        $encabezado = Parameter::where('codigoParametro', 'CATALOGO_ENCABEZADO')
            ->whereNull('auditoriaFechaEliminacion')
            ->first();

        $encabezadoImagen = $this->procesarImagen($encabezado?->nombre, 'https://via.placeholder.com/800x100.png?text=Catálogo+de+Productos');

        // Obtener imagen de portada
        $portada = Parameter::where('codigoParametro', 'CATALOGO_PORTADA')
            ->whereNull('auditoriaFechaEliminacion')
            ->first();

        $portadaImagen = $this->procesarImagen($portada?->nombre, 'https://via.placeholder.com/800x1200.png?text=Portada+Catálogo');

        // Aplicar filtros a productos
        $query = Product::with('imagenes');

        if ($this->request->filled('categoria_id')) {
            $query->where('categoria_id', $this->request->categoria_id);
        }

        if ($this->request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $this->request->nombre . '%');
        }

        if ($this->request->filled('stock')) {
            $query->where('stock', '>=', $this->request->stock);
        }

        $productos = $query->get()
            ->values()
            ->map(function ($producto, $index) use ($categorias) {
                $imagenPrincipal = $producto->imagenes
                    ->where('es_principal', true)
                    ->first()?->imagen_url;

                $base64Imagen = $this->procesarImagen($imagenPrincipal, public_path('images/placeholder50.png'), true);

                return array_filter([
                    'nro' => $index + 1,
                    'modelo' => $producto->codigo,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => number_format($producto->precio, 2),
                    'categoria' => $categorias[$producto->categoria_id] ?? '-',
                    'imagen_url' => $base64Imagen,
                ]);
            });

        $productosAgrupados = $productos->groupBy('categoria');

        // Datos de empresa
        $nombreEmpresa = $this->getParametro('EMPRESA_NOMBRE');
        $telefonoEmpresa = $this->getParametro('EMPRESA_NUMERO_TELEFONO');
        $webEmpresa = $this->getParametro('EMPRESA_PAGINA_WEB');
        $direccionEmpresa = $this->getParametro('EMPRESA_DIRECCION');
        $correoEmpresa = $this->getParametro('EMPRESA_CORREO');

        // Generar PDF
        $pdf = Pdf::loadView('pdf.catalogo-product', [
            'productosAgrupados' => $productosAgrupados,
            'encabezadoImagen' => $encabezadoImagen,
            'portadaImagen' => $portadaImagen,
            'nombreEmpresa' => $nombreEmpresa,
            'telefonoEmpresa' => $telefonoEmpresa,
            'webEmpresa' => $webEmpresa,
            'direccionEmpresa' => $direccionEmpresa,
            'correoEmpresa' => $correoEmpresa,
        ]);

        return $pdf->download('catalogo_productos.pdf');
    }

    private function procesarImagen(?string $path, string $default, bool $isStorage = false): string
    {
        if (!$path) return $default;

        $ruta = $isStorage
            ? storage_path('app/public/' . $path)
            : storage_path('app/public/' . $path);

        if (file_exists($ruta)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($ruta));
        }

        return $default;
    }

    private function getParametro(string $codigo): string
    {
        return Parameter::where('codigoParametro', $codigo)
            ->whereNull('auditoriaFechaEliminacion')
            ->first()?->nombre ?? ' ';
    }
}
