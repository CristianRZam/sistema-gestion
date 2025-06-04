<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $userId = 1; // Cambia si tienes un usuario específico

        $categorias = [1, 2, 3]; // IDs de categorías existentes

        for ($i = 1; $i <= 40; $i++) {
            Product::create([
                'codigo' => 'P' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nombre' => 'Producto ' . $i,
                'descripcion' => 'Descripción del producto ' . $i,
                'precio' => mt_rand(1000, 50000) / 100,
                'stock' => mt_rand(0, 100),
                'categoria_id' => $categorias[array_rand($categorias)],
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ]);
        }
    }
}
