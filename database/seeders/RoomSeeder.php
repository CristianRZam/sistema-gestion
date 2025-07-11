<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $userId = 1; // Ajusta esto según tu sistema de autenticación

        $pisos = Parameter::where('codigoParametro', 'PISO_HABITACION')->get();
        $tipos = Parameter::where('codigoParametro', 'TIPO_HABITACION')->pluck('idParametro')->toArray();
        $estados = Parameter::where('codigoParametro', 'ESTADO_HABITACION')->pluck('idParametro')->toArray();

        // Contador de números por piso
        $numerosPorPiso = [];

        $totalHabitaciones = 30;

        for ($i = 0; $i < $totalHabitaciones; $i++) {
            // Piso aleatorio
            $piso = $pisos->random();
            $pisoOrden = $piso->orden;
            $pisoId = $piso->idParametro;

            // Obtener número disponible para ese piso
            if (!isset($numerosPorPiso[$pisoId])) {
                $numerosPorPiso[$pisoId] = 1;
            }

            $numero = ($pisoOrden * 100) + $numerosPorPiso[$pisoId];
            $numerosPorPiso[$pisoId]++;

            Room::create([
                'numero' => (string) $numero,
                'tipo_id' => fake()->randomElement($tipos),
                'piso_id' => $pisoId,
                'capacidad' => fake()->numberBetween(1, 4),
                'precio' => fake()->randomFloat(2, 80, 300),
                'precio_promocion' => fake()->optional(0.5)->randomFloat(2, 60, 250),
                'estado_id' => fake()->randomElement($estados),
                'descripcion' => fake()->optional()->sentence(8),

                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
            ]);
        }
    }
}
