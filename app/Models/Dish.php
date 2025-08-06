<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    // Nombre explícito de la tabla
    protected $table = 'dishes';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'categoria_id',
        'codigo',
        'nombre',
        'descripcion',
        'precio',
        'precio_promocion',
        'activo',
        'slug',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Casts para tipos de datos
    protected $casts = [
        'precio' => 'decimal:2',
        'precio_promocion' => 'decimal:2',
        'activo' => 'boolean',
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
    ];

    public function categoria()
    {
        return $this->belongsTo(Parameter::class, 'categoria_id', 'idParametro')
            ->where('codigoParametro', 'CATEGORIA_PLATILLO');
    }

    public function imagenes()
    {
        return $this->hasMany(DishImage::class, 'dish_id');
    }

    public $timestamps = false;
}
