<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;
    protected $table = 'payments';

    // Campos que se pueden llenar en masa
    protected $fillable = [
        'pagable_id',
        'pagable_type',
        'monto_pagado',
        'metodo_pago_id',
        'estado_pago_id',
        'fecha_pago',
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

    public function pagable()
    {
        return $this->morphTo();
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(Parameter::class, 'metodo_pago_id', 'idParametro')
            ->where('codigoParametro', 'METODO_PAGO');
    }


}
