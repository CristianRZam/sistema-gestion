<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;
    protected $table = 'reservations';

    // Campos que se pueden llenar en masa
    protected $fillable = [
        'customer_id',
        'fecha_reserva',
        'fecha_inicio',
        'fecha_fin',
        'estado_id',
        'monto_total',
        'pagado',
        'notas',
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

    public function estado()
    {
        return $this->belongsTo(Parameter::class, 'estado_id', 'idParametro')
            ->where('codigoParametro', 'ESTADO_RESERVA');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'reservation_rooms', 'reservation_id', 'room_id');
    }

}
