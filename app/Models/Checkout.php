<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;
    protected $table = 'checkouts';

    // Campos que se pueden llenar en masa
    protected $fillable = [
        'reservation_room_id',
        'fecha_checkout',
        'user_id',
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
