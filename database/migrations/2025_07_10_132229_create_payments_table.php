<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id()->comment('Identificador único del pago');; // ID único del pago

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->comment('Reserva asociada al pago');

            $table->decimal('monto_total', 10, 2)
                ->comment('Monto total pagado por la reserva');

            $table->unsignedBigInteger('metodo_pago_id')
                ->comment('Método de pago utilizado (efectivo, tarjeta, etc). Se relaciona lógicamente con tabla de parámetros');

            $table->unsignedBigInteger('estado_pago_id')
                ->comment('Estado del pago (pagado, pendiente, anulado, etc). Se relaciona lógicamente con tabla de parámetros');

            $table->dateTime('fecha_pago')
                ->comment('Fecha y hora en que se realizó el pago');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->comment('Usuario del sistema que registró el pago (por ejemplo, recepcionista)');

            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')
                ->nullable()
                ->comment('Fecha de creación del registro');

            $table->unsignedBigInteger('auditoriaCreadoPor')
                ->nullable()
                ->comment('Usuario que creó el registro');

            $table->dateTime('auditoriaFechaModificacion')
                ->nullable()
                ->comment('Fecha de la última modificación');

            $table->unsignedBigInteger('auditoriaModificadoPor')
                ->nullable()
                ->comment('Usuario que modificó el registro');

            $table->dateTime('auditoriaFechaEliminacion')
                ->nullable()
                ->comment('Fecha de eliminación lógica');

            $table->unsignedBigInteger('auditoriaEliminadoPor')
                ->nullable()
                ->comment('Usuario que eliminó el registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
