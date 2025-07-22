<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;
    protected $table = 'services';

    // Campos que se pueden llenar en masa
    protected $fillable = [
        'nombre',
        'precio',
        'descripcion',
        'activo',
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

    public function imagenes()
    {
        return $this->hasMany(ServiceImage::class, 'service_id');
    }

    public function imagenPrincipal()
    {
        return $this->hasOne(ServiceImage::class)->where('es_principal', true);
    }
}
