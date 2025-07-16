<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id()->comment('ID único del checkin');

            $table->foreignId('reservation_room_id')
                ->constrained('reservation_rooms')
                ->comment('Detalle de reserva asociada al check-in');

            $table->dateTime('fecha_checkin')
                ->comment('Fecha y hora en que se realizó el check-in');

            $table->foreignId('user_id')
                ->nullable()
                ->comment('Usuario que registró el check-in (puede ser recepcionista)');

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
        Schema::dropIfExists('checkins');
    }
};
