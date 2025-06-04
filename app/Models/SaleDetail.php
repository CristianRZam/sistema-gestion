<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sale;
use App\Models\Product;

class SaleDetail extends Model
{
    // Campos asignables masivamente
    public $timestamps = false;
    protected $fillable = [
        'sale_id',
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
    public function sale()
    {
        return $this->belongsTo(Sale::class);
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
