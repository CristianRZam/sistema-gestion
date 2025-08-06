<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    // Nombre de la tabla (opcional si sigue la convención)
    protected $table = 'restaurant_tables';

    // No usar timestamps automáticos (usas auditoría propia)
    public $timestamps = false;

    // Asignación masiva
    protected $fillable = [
        'codigo',
        'nombre',
        'piso_id',
        'capacidad',
        'activa',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Casts
    protected $casts = [
        'piso_id' => 'integer',
        'capacidad' => 'integer',
        'activa' => 'boolean',
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
    ];
}
