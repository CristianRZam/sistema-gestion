<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetailItem extends Model
{
    // Si tu tabla se llama diferente, define $table.
    protected $table = 'order_detail_items';

    // No usar created_at/updated_at (usas auditoría propia)
    public $timestamps = false;

    // Columns que se pueden asignar masivamente
    protected $fillable = [
        'restaurant_order_id',
        'dish_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        // auditoría si quieres asignarla por mass-assignment
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Casts
    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
    ];

    /**
     * Relaciones
     */

    /**
     * Orden padre
     */
    public function orden(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class, 'restaurant_order_id', 'id');
    }

    /**
     * Platillo asociado
     */
    public function platillo(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'dish_id', 'id');
    }


}
