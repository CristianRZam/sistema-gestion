<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseLoss extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_detail_id',
        'cantidad_fallida',
        'motivo_id',
        'tipo_id',
        'fecha_perdida',
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    protected $casts = [
        'fecha_perdida' => 'date',
        'auditoriaFechaCreacion' => 'date',
        'auditoriaFechaModificacion' => 'date',
        'auditoriaFechaEliminacion' => 'date',
    ];

    public function motivo()
    {
        return $this->belongsTo(Parameter::class, 'motivo_id', 'idParametro')
            ->where('codigoParametro', 'MOTIVO_PERDIDA_COMPRA');
    }

    public function tipo()
    {
        return $this->belongsTo(Parameter::class, 'tipo_id', 'idParametro')
            ->where('codigoParametro', 'TIPO_PERDIDA_COMPRA');
    }


    public function purchaseDetail()
    {
        return $this->belongsTo(PurchaseDetail::class);
    }
}
