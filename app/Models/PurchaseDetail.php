<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    // Campos asignables masivamente
    public $timestamps = false;
    protected $fillable = [
        'purchase_id',
        'product_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor'
    ];

    // Relación con la venta
    public function Purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Casting de atributos para asegurar el formato correcto al acceder a ellos.
     */
    protected $casts = [
        'auditoriaFechaCreacion' => 'date',
        'auditoriaFechaModificacion' => 'date',
        'auditoriaFechaEliminacion' => 'date',
    ];
}
