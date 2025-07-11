<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id()->comment('ID único de la reserva');

            // Relaciones
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->onDelete('cascade')
                ->comment('Cliente que realiza la reserva');

            $table->dateTime('fecha_reserva')->comment('Fecha y hora de la reserva');
            $table->dateTime('fecha_inicio')->comment('Fecha y hora de inicio de la reserva');
            $table->dateTime('fecha_fin')->comment('Fecha y hora de fin de la reserva');

            // Estado de la reserva (reservado, cancelado, no show, etc.)
            $table->unsignedBigInteger('estado_id')->comment('Estado lógico de la reserva (relación con parámetros)');

            $table->decimal('monto_total', 10, 2)->default(0)
                ->comment('Monto total estimado de la reserva (precio de habitaciones, servicios, etc.)');

            // Información de pago
            $table->boolean('pagado')->default(false)->comment('Indica si la reserva ha sido pagada');

            // Notas u observaciones adicionales
            $table->text('notas')->nullable()->comment('Notas adicionales sobre la reserva');

            $table->foreignId('user_id')
                ->nullable()->comment('Usuario que registró la reserva');

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
        Schema::dropIfExists('reservations');
    }
};
