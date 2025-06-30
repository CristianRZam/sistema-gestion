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
        Schema::create('purchase_losses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_detail_id'); // Producto específico de la compra
            $table->integer('cantidad_fallida'); // Cantidad que se perdió
            $table->unsignedBigInteger('motivo_id')->nullable(); // Ej: vencido, dañado, etc.
            $table->unsignedBigInteger('tipo_id')->nullable(); // Ej: perdida, devolucion.
            $table->date('fecha_perdida'); // Fecha en que se detectó la pérdida

            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')->nullable();
            $table->string('auditoriaCreadoPor')->nullable();
            $table->dateTime('auditoriaFechaModificacion')->nullable();
            $table->string('auditoriaModificadoPor')->nullable();
            $table->dateTime('auditoriaFechaEliminacion')->nullable();
            $table->string('auditoriaEliminadoPor')->nullable();

            // Relaciones
            $table->foreign('purchase_detail_id')->references('id')->on('purchase_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_losses');
    }
};
