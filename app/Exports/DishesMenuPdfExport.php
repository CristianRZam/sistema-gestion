<?php

namespace App\Exports;

use App\Models\Dish;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DishesMenuPdfExport
{
    protected Request $request;
    protected string $view = 'pdf.carta-dish';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download(string $filename = 'menu.pdf')
    {
        $data = $this->getData();

        return Pdf::loadView($this->view, $data)
            ->setPaper('A4', 'portrait')
            ->download($filename);
    }

    protected function getData()
    {
        $categorias = Parameter::where('codigoParametro', 'CATEGORIA_PLATILLO')
            ->whereNull('auditoriaFechaEliminacion')
            ->pluck('nombre', 'idParametro');

        $query = Dish::with(['imagenes' => function($q) {
            $q->where('es_principal', true);
        }])->whereNull('auditoriaFechaEliminacion');

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
            $query->where('activo', (bool) $this->request->estado);
        }

        $platillos = $query->get();

        $agrupados = [];
        foreach ($platillos as $platillo) {
            $categoriaNombre = $categorias[$platillo->categoria_id] ?? 'Sin Categoría';
            $imagenRuta = optional($platillo->imagenes->first())->imagen_url;

            $imagenBase64 = $this->procesarImagen($imagenRuta, public_path('images/placeholder50.png'), true);

            $agrupados[$categoriaNombre][] = [
                'nombre'           => $platillo->nombre,
                'descripcion'      => $platillo->descripcion,
                'precio'           => number_format($platillo->precio, 2),
                'precio_promocion' => $platillo->precio_promocion !== null
                    ? number_format($platillo->precio_promocion, 2)
                    : null,
                'activo'           => $platillo->activo,
                'imagen'           => $imagenBase64,
            ];
        }

        return ['categoriasConPlatillos' => $agrupados];
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
}
