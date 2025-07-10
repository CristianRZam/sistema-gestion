<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    // Laravel no manejará automáticamente created_at y updated_at
    public $timestamps = false;
    protected $table = 'rooms';


    // Campos que se pueden llenar en masa
    protected $fillable = [
        'numero',
        'tipo_id',
        'piso_id',
        'capacidad',
        'precio',
        'precio_promocion',
        'estado_id',
        'descripcion',
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

    public function tipo()
    {
        return $this->belongsTo(Parameter::class, 'tipo_id', 'idParametro')
            ->where('codigoParametro', 'TIPO_HABITACION');
    }

    public function piso()
    {
        return $this->belongsTo(Parameter::class, 'piso_id', 'idParametro')
            ->where('codigoParametro', 'PISO_HABITACION');
    }

    public function estado()
    {
        return $this->belongsTo(Parameter::class, 'estado_id', 'idParametro')
            ->where('codigoParametro', 'ESTADO_HABITACION');
    }

}
