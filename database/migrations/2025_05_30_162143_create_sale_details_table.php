<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'sale_details'.
     */
    public function up(): void
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();

            // Venta relacionada
            $table->unsignedBigInteger('sale_id');

            // Producto vendido
            $table->unsignedBigInteger('product_id');

            // Cantidad vendida
            $table->integer('cantidad');

            // Precio unitario al momento de la venta
            $table->decimal('precio_unitario', 10, 2);

            // Subtotal = cantidad * precio_unitario
            $table->decimal('subtotal', 10, 2);

            // Auditoría (opcional)
            $table->date('auditoriaFechaCreacion')->nullable();
            $table->string('auditoriaCreadoPor')->nullable();
            $table->date('auditoriaFechaModificacion')->nullable();
            $table->string('auditoriaModificadoPor')->nullable();
            $table->date('auditoriaFechaEliminacion')->nullable();
            $table->string('auditoriaEliminadoPor')->nullable();

            // Relaciones
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
