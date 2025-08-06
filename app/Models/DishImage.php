<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishImage extends Model
{
    // Nombre explícito de la tabla
    protected $table = 'dish_images';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'dish_id',
        'imagen_url',
        'es_principal',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Casts para tipos de datos
    protected $casts = [
        'es_principal' => 'boolean',
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
    ];

    // Relación con el platillo
    public function platillo()
    {
        return $this->belongsTo(Dish::class, 'dish_id');
    }

    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;
}
