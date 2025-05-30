<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Customer
 *
 * Representa a un cliente en el sistema, incluyendo información básica
 * y campos de auditoría personalizados para registrar la creación,
 * modificación y eliminación lógica de registros.
 */
class Customer extends Model
{
    // Desactiva los timestamps automáticos de Laravel (created_at, updated_at)
    public $timestamps = false;

    /**
     * Atributos que se pueden asignar de manera masiva.
     * Se incluyen los campos básicos del cliente y los campos de auditoría.
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
