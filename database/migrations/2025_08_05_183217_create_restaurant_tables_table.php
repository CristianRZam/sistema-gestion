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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id()->comment('Identificador único de la mesa');
            $table->string('codigo')->unique()->comment('Código único de la mesa, ej: M01');
            $table->string('nombre')->nullable()->comment('Nombre o alias de la mesa, ej: Terraza 1');

            // Piso de la mesa (relación lógica a parameters)
            $table->unsignedBigInteger('piso_id')->nullable()->comment('Parametro: piso al que pertenece la mesa');

            $table->integer('capacidad')->default(4)->comment('Número de personas que caben en la mesa');
            $table->boolean('activa')->default(true)->comment('Indica si la mesa está en uso o fuera de servicio');

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
        Schema::dropIfExists('restaurant_tables');
    }
};
