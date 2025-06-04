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

            // Usuario (vendedor) que realizó la venta (relación lógica, sin clave foránea)
            $table->unsignedBigInteger('usuario_id')->nullable();

            // Fecha de la venta
            $table->dateTime('fecha_venta');

            // Total de la venta
            $table->decimal('total', 10, 2);

            // Descuento de la venta
            $table->decimal('descuento', 10, 2)->default(0);

            // Método de pago (opcional)
            $table->unsignedBigInteger('metodo_pago_id')->nullable();

            // Monto con el que pagó el cliente
            $table->decimal('pago_con', 10, 2)->nullable();

            // Vuelto entregado al cliente
            $table->decimal('vuelto', 10, 2)->default(0);

            // Estado de la venta (ej. completada, anulada)
            $table->unsignedBigInteger('estado_venta_id')->nullable();

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
