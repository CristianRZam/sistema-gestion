<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParameterSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $userId = 1;

        Parameter::insert([
            [
                'idParametroPadre' => null,
                'idParametro' => 1,
                'tipo' => 'CATEGORIA',
                'nombre' => 'Electrónica',
                'nombreCorto' => 'Electro',
                'orden' => 1,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
            [
                'idParametroPadre' => null,
                'idParametro' => 2,
                'tipo' => 'CATEGORIA',
                'nombre' => 'Ropa',
                'nombreCorto' => 'Ropa',
                'orden' => 2,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
            [
                'idParametroPadre' => null,
                'idParametro' => 3,
                'tipo' => 'CATEGORIA',
                'nombre' => 'Alimentos',
                'nombreCorto' => 'Alim',
                'orden' => 3,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
        ]);


        // METODO DE PAGO
        Parameter::insert([
            [
                'idParametroPadre' => null,
                'idParametro' => 1,
                'tipo' => 'METODO_PAGO',
                'nombre' => 'Efectivo',
                'nombreCorto' => 'Efectivo',
                'orden' => 1,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
            [
                'idParametroPadre' => null,
                'idParametro' => 2,
                'tipo' => 'METODO_PAGO',
                'nombre' => 'Yape',
                'nombreCorto' => 'Yape',
                'orden' => 4,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
            [
                'idParametroPadre' => null,
                'idParametro' => 3,
                'tipo' => 'METODO_PAGO',
                'nombre' => 'Plin',
                'nombreCorto' => 'Plin',
                'orden' => 2,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
            [
                'idParametroPadre' => null,
                'idParametro' => 4,
                'tipo' => 'METODO_PAGO',
                'nombre' => 'Transferencia Bancaria',
                'nombreCorto' => 'Transferencia',
                'orden' => 3,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
        ]);



        // ESTADO DE VENTA

        Parameter::insert([
            [
                'idParametroPadre' => null,
                'idParametro' => 1,
                'tipo' => 'ESTADO_VENTA',
                'nombre' => 'Pendiente',
                'nombreCorto' => 'Pendiente',
                'orden' => 2,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
            [
                'idParametroPadre' => null,
                'idParametro' => 2,
                'tipo' => 'ESTADO_VENTA',
                'nombre' => 'Pagada',
                'nombreCorto' => 'Pagada',
                'orden' => 1,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
            [
                'idParametroPadre' => null,
                'idParametro' => 3,
                'tipo' => 'ESTADO_VENTA',
                'nombre' => 'Cancelada',
                'nombreCorto' => 'Cancelada',
                'orden' => 3,
                'auditoriaFechaCreacion' => $now,
                'auditoriaCreadoPor' => $userId,
                'auditoriaFechaModificacion' => null,
                'auditoriaModificadoPor' => null,
                'auditoriaFechaEliminacion' => null,
                'auditoriaEliminadoPor' => null,
            ],
        ]);

    }
}
