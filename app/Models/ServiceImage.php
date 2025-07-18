<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceImage extends Model
{
    // Desactiva timestamps automáticos (created_at y updated_at)
    public $timestamps = false;
    // 📝 Campos que se pueden asignar de forma masiva (mass assignment)
    protected $fillable = [
        'service_id',                // ID del producto asociado
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

    /**
     * 🔗 Relación: Una imagen pertenece a un servicio.
     */
    public function servicio()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    protected $casts = [
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
    ];
}
