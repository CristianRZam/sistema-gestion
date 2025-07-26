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
        'monto_entregado',
        'vuelto',
        'metodo_pago_id',
        'estado_pago_id',
        'fecha_pago',
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

    public function pagable()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(Parameter::class, 'metodo_pago_id', 'idParametro')
            ->where('codigoParametro', 'METODO_PAGO');
    }

    public function estadoPago()
    {
        return $this->belongsTo(Parameter::class, 'estado_pago_id', 'idParametro')
            ->where('codigoParametro', 'ESTADO_PAGO');
    }
    // Solo traer los que no están eliminados
    protected static function booted()
    {
        static::addGlobalScope('no_eliminado', function ($builder) {
            $builder->whereNull('auditoriaFechaEliminacion');
        });
    }



}
