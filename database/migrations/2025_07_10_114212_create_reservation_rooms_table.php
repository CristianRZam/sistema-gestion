<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservation_rooms', function (Blueprint $table) {
            $table->id()->comment('ID del detalle de habitación en reserva');

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->onDelete('cascade')->comment('ID foráneo de la reserva');

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->onDelete('cascade')->comment('ID foráneo de la habitación');

            $table->integer('cantidad_personas')->default(1)->comment('N° de personas en la habitación');
            $table->decimal('precio', 10, 2)->comment('Precio de la habitación en esta reserva');


            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')->nullable();
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable();
            $table->dateTime('auditoriaFechaModificacion')->nullable();
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable();
            $table->dateTime('auditoriaFechaEliminacion')->nullable();
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_rooms');
    }
};
