<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class RestaurantOrder extends Model
{
    // Si tu tabla se llama distinto, cámbialo aquí:
    protected $table = 'restaurant_orders';

    // No usaremos created_at / updated_at automáticos (usas auditoría propia)
    public $timestamps = false;

    // Mass assignment — ajusta según las columnas que quieras poder asignar en mass-assignment
    protected $fillable = [
        'codigo',
        'customer_id',
        'estado_id',
        'tipo_entrega_id',
        'pagado',
        'modo_pago_id',
        'notas',
        'usuario_id',
        'descuento',
        'total',
        'fecha',
        // auditoría si la quieres llenar por mass-assignment
        'auditoriaFechaCreacion',
        'auditoriaCreadoPor',
        'auditoriaFechaModificacion',
        'auditoriaModificadoPor',
        'auditoriaFechaEliminacion',
        'auditoriaEliminadoPor',
    ];

    // Casts para tipos nativos
    protected $casts = [
        'pagado' => 'boolean',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha' => 'datetime',
        'auditoriaFechaCreacion' => 'datetime',
        'auditoriaFechaModificacion' => 'datetime',
        'auditoriaFechaEliminacion' => 'datetime',
    ];

    /**
     * Relaciones
     */

    // Items de la orden
    public function items(): HasMany
    {
        return $this->hasMany(OrderDetailItem::class, 'restaurant_order_id', 'id');
    }

    // Mesas asociadas (tabla pivote restaurant_order_tables)
    public function mesas(): BelongsToMany
    {
        return $this->belongsToMany(
            RestaurantTable::class,
            'restaurant_order_tables',
            'restaurant_order_id',
            'restaurant_table_id'
        );
    }

    // Pagos (polimórfico: payments.pagable_type / pagable_id)
    public function pagos()
    {
        return $this->morphMany(Payment::class, 'pagable');
    }

    // Cliente (si tienes un modelo Customer)
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // Usuario / mesero que creó/modificó
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    /**
     * Relaciones lógicas a parámetros (ESTADO, TIPO ENTREGA, MODO PAGO)
     *
     * NOTA: si tu tabla parameters usa 'idParametro' como PK en vez de 'id',
     * cambia el tercer argumento del belongsTo por 'idParametro'.
     */
    public function estado()
    {
        return $this->belongsTo(Parameter::class, 'estado_id', 'idParametro')
            ->where('codigoParametro', 'ESTADO_PEDIDO_RESTAURANTE');
    }

    public function tipoEntrega(): BelongsTo
    {
        return $this->belongsTo(Parameter::class, 'tipo_entrega_id', 'idParametro')
            ->where('codigoParametro', 'TIPO_ENTREGA_RESTAURANTE');
    }

    public function modoPago(): BelongsTo
    {
        return $this->belongsTo(Parameter::class, 'modo_pago_id', 'idParametro')
            ->where('codigoParametro', 'MODO_PAGO');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->codigo)) {
                $model->codigo = static::generateCodigo();
            }
            // Si quieres establecer auditoría por defecto, puedes hacerlo aquí
            if (empty($model->auditoriaFechaCreacion)) {
                $model->auditoriaFechaCreacion = now();
            }
        });
    }

    protected static function generateCodigo(): string
    {
        // Formato ejemplo: RO-YYYYMMDD-<idTempRandom>
        return 'RO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
