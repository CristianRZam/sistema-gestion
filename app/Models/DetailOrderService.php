<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailOrderService extends Model
{
    // Inhabilitar created_at y updated_at
    public $timestamps = false;

    protected $table = 'detail_order_services';

    protected $fillable = [
        'order_service_id',
        'service_id',
        'descuento',
        'cantidad',
        'precio_unitario',
        'subtotal',
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
    ];

    public function servicio()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

}
