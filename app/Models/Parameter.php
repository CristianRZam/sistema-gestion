<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    // Inhabilitar created_at y updated_at
    public $timestamps = false;

    protected $table = 'parameters';

    protected $fillable = [
        'idParametroPadre',
        'idParametro',
        'codigoParametro',
        'tipo',
        'nombre',
        'nombreCorto',
        'orden',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Convertir las fechas automáticamente a objetos Date
    protected $dates = [
        'auditoriaFechaCreacion',
        'auditoriaFechaModificacion',
        'auditoriaFechaEliminacion',
    ];
}
