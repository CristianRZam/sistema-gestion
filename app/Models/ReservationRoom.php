<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationRoom extends Model
{
    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;
    protected $table = 'reservation_rooms';


    // Campos que se pueden llenar en masa
    protected $fillable = [
        'reservation_id',
        'room_id',
        'cantidad_personas',
        'fecha_inicio',
        'fecha_fin',
        'precio',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];
    protected $casts = [
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
    ];

    // app/Models/ReservationRoom.php
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

}
