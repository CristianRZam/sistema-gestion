<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderService extends Model
{
    // Inhabilitar created_at y updated_at
    public $timestamps = false;

    protected $table = 'order_services';

    protected $fillable = [
        'customer_id',
        'reservation_room_id',
        'fecha',
        'descuento',
        'total',
        'modo_pago_id',
        'estado_id',
        'usuario_id',
        'pagado',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Convertir las fechas automáticamente a objetos Date
    protected $casts = [
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
        'fecha' => 'datetime',
    ];

    // Relación con el cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación con el detalle de venta (uno a muchos)
    public function detalles()
    {
        return $this->hasMany(DetailOrderService::class);
    }

    public function encargado()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Sale.php
    public function estado()
    {
        return $this->belongsTo(Parameter::class, 'estado_id', 'idParametro')
            ->where('codigoParametro', 'ESTADO_ORDEN_SERVICIO');
    }

    public function pagos()
    {
        return $this->morphMany(Payment::class, 'pagable');
    }

    public function reservationRoom()
    {
        return $this->belongsTo(ReservationRoom::class);
    }

}
