<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'sales'.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Cliente asociado a la venta
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->unsignedBigInteger('reservation_room_id')->nullable()->comment('ID del detalle de habitación asociado (relación lógica)');

            // Usuario (vendedor) que realizó la venta (relación lógica, sin clave foránea)
            $table->unsignedBigInteger('usuario_id')->nullable();

            // Fecha de la venta
            $table->dateTime('fecha_venta')->nullable();

            // Total de la venta
            $table->decimal('total', 10, 2)->default(0)->comment('Total de la venta');

            // Descuento de la venta
            $table->decimal('descuento', 10, 2)->default(0);

            // Estado de la venta (ej. completada, anulada)
            $table->unsignedBigInteger('estado_venta_id')->nullable();

            $table->boolean('pagado')->default(false)->comment('Indica si ha sido pagada');
            $table->unsignedBigInteger('modo_pago_id')->default(1)->comment('Indica el modo de pago: completo o en partes');

            // Auditoría
            $table->date('auditoriaFechaCreacion')->nullable();
            $table->string('auditoriaCreadoPor')->nullable();
            $table->date('auditoriaFechaModificacion')->nullable();
            $table->string('auditoriaModificadoPor')->nullable();
            $table->date('auditoriaFechaEliminacion')->nullable();
            $table->string('auditoriaEliminadoPor')->nullable();

            // Claves foráneas lógicas
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            // Nota: usuario_id no tiene restricción de clave foránea (relación lógica)
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
