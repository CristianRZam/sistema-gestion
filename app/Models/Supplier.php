<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Supplier
 *
 * Representa a un proveedor en el sistema. Este modelo
 * se vincula con la tabla 'suppliers' y contiene información
 * como nombre, RUC, teléfono, email y dirección, además de
 * los campos de auditoría personalizados.
 */
class Supplier extends Model
{
    /**
     * Indica que no se usarán los timestamps automáticos
     * (created_at y updated_at) de Laravel.
     */
    public $timestamps = false;

    /**
     * Atributos que pueden asignarse de forma masiva.
     */
    protected $fillable = [
        'nombre',
        'documento',
        'telefono',
        'email',
        'direccion',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    /**
     * Casting de atributos para asegurar el formato correcto al acceder a ellos.
     */
    protected $casts = [
        'auditoriaFechaCreacion' => 'date',
        'auditoriaFechaModificacion' => 'date',
        'auditoriaFechaEliminacion' => 'date',
    ];
}
