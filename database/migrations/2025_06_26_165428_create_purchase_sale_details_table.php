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
        Schema::create('purchase_sale_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_detail_id');
            $table->foreign('purchase_detail_id')->references('id')->on('purchase_details')->onDelete('cascade');

            // Relación con el detalle de venta
            $table->unsignedBigInteger('sale_detail_id');
            $table->foreign('sale_detail_id')->references('id')->on('sale_details')->onDelete('cascade');

            // Cuánto stock se usó de ese lote
            $table->integer('cantidad_utilizada');

            // Precio de costo unitario en esa compra (puede usarse para calcular utilidad)
            $table->decimal('costo_unitario', 10, 2)->nullable();

            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')->nullable();
            $table->string('auditoriaCreadoPor')->nullable();
            $table->dateTime('auditoriaFechaModificacion')->nullable();
            $table->string('auditoriaModificadoPor')->nullable();
            $table->dateTime('auditoriaFechaEliminacion')->nullable();
            $table->string('auditoriaEliminadoPor')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_sale_details');
    }
};
