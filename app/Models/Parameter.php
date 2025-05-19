<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    // Si no usas timestamps
    // public $timestamps = false;

    protected $table = 'parameters';

    protected $fillable = [
        'idParametroPadre',
        'idParametro',
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
}
