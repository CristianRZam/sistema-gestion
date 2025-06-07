<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = [
        'supplier_id',
        'usuario_id',
        'fecha_compra',
        'total',
        'descuento',
        'metodo_pago_id',
        'estado_compra_id',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor'
    ];

    // Relación con el proveedor
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relación con el detalle de venta (uno a muchos)
    public function detalles()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    // Relación lógica con el usuario (comprador) - opcional
    public function comprador()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Purchase.php
    public function estadoCompra()
    {
        return $this->belongsTo(Parameter::class, 'estado_compra_id', 'idParametro')
            ->where('tipo', 'ESTADO_COMPRA');
    }

    /**
     * Casting de atributos para asegurar el formato correcto al acceder a ellos.
     */
    protected $casts = [
        'auditoriaFechaCreacion' => 'date',
        'auditoriaFechaModificacion' => 'date',
        'auditoriaFechaEliminacion' => 'date',
        'fecha_compra'=>'datetime',
    ];
}
