<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\SaleDetail;
use App\Models\User;

class Sale extends Model
{
    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = [
        'customer_id',
        'usuario_id',
        'fecha_venta',
        'total',
        'descuento',
        'pago_con',
        'vuelto',
        'metodo_pago_id',
        'estado_venta_id',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor'
    ];

    // Relación con el cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación con el detalle de venta (uno a muchos)
    public function detalles()
    {
        return $this->hasMany(SaleDetail::class);
    }

    // Relación lógica con el usuario (vendedor) - opcional
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Sale.php
    public function estadoVenta()
    {
        return $this->belongsTo(Parameter::class, 'estado_venta_id', 'idParametro')
            ->where('tipo', 'ESTADO_VENTA');
    }

    /**
     * Casting de atributos para asegurar el formato correcto al acceder a ellos.
     */
    protected $casts = [
        'auditoriaFechaCreacion' => 'date',
        'auditoriaFechaModificacion' => 'date',
        'auditoriaFechaEliminacion' => 'date',
        'fecha_venta'=>'datetime',
    ];
}
