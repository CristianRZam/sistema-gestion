<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dish_images', function (Blueprint $table) {
            $table->id()->comment('Identificador único de la imagen platillo');;
            $table->foreignId('dish_id')->constrained('dishes')->onDelete('cascade')->comment('Clave foránea del platillo');
            $table->string('imagen_url')->comment('Ruta o URL de la imagen del platillo');
            $table->boolean('es_principal')->default(false)->comment('Indica si es la imagen principal');

            // Campos de auditoría (opcional, si no se usan timestamps automáticos)
            $table->dateTime('auditoriaFechaCreacion')->nullable()->comment('Fecha de creación');
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable()->comment('Usuario que creó');
            $table->dateTime('auditoriaFechaModificacion')->nullable()->comment('Fecha de modificación');
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable()->comment('Usuario que modificó');
            $table->dateTime('auditoriaFechaEliminacion')->nullable()->comment('Eliminación lógica');
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable()->comment('Usuario que eliminó');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_images');
    }
};
