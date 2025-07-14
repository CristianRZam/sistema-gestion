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

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->onDelete('cascade')
                ->comment('Cliente que realiza la reserva');


            $table->dateTime('fecha_reserva')->nullable()->comment('Fecha y hora en que se registró la reserva');

            $table->unsignedBigInteger('estado_id')->comment('Estado lógico de la reserva (reservado, cancelado, etc.)');

            $table->decimal('monto_total', 10, 2)->default(0)->comment('Monto total estimado');

            $table->boolean('pagado')->default(false)->comment('Indica si ha sido pagada');

            $table->text('notas')->nullable()->comment('Notas adicionales');

            $table->foreignId('user_id')->nullable()->comment('Usuario que registró');

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
