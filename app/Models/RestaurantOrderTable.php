<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantOrderTable extends Model
{
    // Nombre de la tabla si quieres dejarlo explícito
    protected $table = 'restaurant_order_tables';

    // No usar created_at/updated_at (auditoría propia)
    public $timestamps = false;

    // Asignación masiva
    protected $fillable = [
        'restaurant_order_id',
        'restaurant_table_id',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Casts
    protected $casts = [
        'restaurant_order_id' => 'integer',
        'restaurant_table_id' => 'integer',
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
    ];

    /**
     * Relaciones
     */

    public function orden(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class, 'restaurant_order_id', 'id');
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id', 'id');
    }
}
