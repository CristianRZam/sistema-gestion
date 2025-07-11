<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id()->comment('Identificador único del pago');

            // Relación con la reserva
            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->onDelete('cascade')
                ->comment('Reserva asociada al pago');

            // Monto pagado en esta transacción
            $table->decimal('monto_pagado', 10, 2)
                ->comment('Monto pagado en esta transacción');

            // Estado del pago (pagado, pendiente, anulado, etc.)
            $table->unsignedBigInteger('estado_pago_id')
                ->comment('Estado del pago. Relación lógica con parámetros');

            // Método de pago (efectivo, tarjeta, transferencia, etc.)
            $table->unsignedBigInteger('metodo_pago_id')
                ->comment('Método de pago. Relación lógica con parámetros');

            // Fecha y hora en que se registró el pago
            $table->dateTime('fecha_pago')
                ->comment('Fecha y hora del pago');

            // Usuario que registró el pago (opcional)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->comment('Usuario del sistema que registró el pago');

            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')->nullable()->comment('Fecha de creación del registro');
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable()->comment('Usuario que creó el registro');

            $table->dateTime('auditoriaFechaModificacion')->nullable()->comment('Fecha de la última modificación');
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable()->comment('Usuario que modificó el registro');

            $table->dateTime('auditoriaFechaEliminacion')->nullable()->comment('Fecha de eliminación lógica');
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable()->comment('Usuario que eliminó el registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
