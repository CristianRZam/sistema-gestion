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
        Schema::create('service_images', function (Blueprint $table) {
            $table->id()->comment('Clave primaria de imagenes de servicio');

            $table->foreignId('service_id')->constrained('services')->onDelete('cascade')->comment('Clave foránea al producto');
            $table->string('imagen_url')->comment('Ruta o URL de la imagen del producto');
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_images');
    }
};
