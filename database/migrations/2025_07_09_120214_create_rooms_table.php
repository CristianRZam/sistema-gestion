<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->comment('Número o código de la habitación');
            $table->unsignedBigInteger('tipo_id')->comment('Tipo (individual, doble, suite, etc.)');
            $table->unsignedBigInteger('piso_id')->comment('Piso donde está la habitación');
            $table->integer('capacidad')->comment('Capacidad máxima de huéspedes');
            $table->decimal('precio', 10, 2)->comment('Tarifa por noche');
            $table->decimal('precio_promocion', 10, 2)->nullable()->comment('Tarifa por noche promoción');
            $table->unsignedBigInteger('estado_id')->default(1)->comment('Disponible, ocupado, limpieza, bloqueada');
            $table->text('descripcion')->nullable()->comment('Descripción o comodidades adicionales');

            // Auditoría
            $table->date('auditoriaFechaCreacion')->nullable()->comment('Fecha de creación (auditoría)');
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable()->comment('Usuario que creó el registro (auditoría)');
            $table->date('auditoriaFechaModificacion')->nullable()->comment('Fecha de última modificación (auditoría)');
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable()->comment('Usuario que modificó el registro (auditoría)');
            $table->date('auditoriaFechaEliminacion')->nullable()->comment('Fecha de eliminación (auditoría)');
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable()->comment('Usuario que eliminó el registro (auditoría)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
