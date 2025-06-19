<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    // 📝 Campos que se pueden asignar de forma masiva (mass assignment)
    protected $fillable = [
        'product_id',                // ID del producto asociado
        'imagen_url',               // Ruta o URL de la imagen
        'es_principal',             // Indica si la imagen es principal

        // Campos de auditoría
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Desactiva timestamps automáticos (created_at y updated_at)
    public $timestamps = false;

    /**
     * 🔗 Relación: Una imagen pertenece a un producto.
     */
    public function producto()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
