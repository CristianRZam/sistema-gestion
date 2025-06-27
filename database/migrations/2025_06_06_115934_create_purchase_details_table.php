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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();

            // Compra relacionada
            $table->unsignedBigInteger('purchase_id');

            // Producto comprado
            $table->unsignedBigInteger('product_id');

            // Cantidad comprada
            $table->integer('cantidad');

            // Precio unitario al momento de la compra
            $table->decimal('precio_unitario', 10, 2);

            // Subtotal = cantidad * precio_unitario
            $table->decimal('subtotal', 10, 2);

            // Stock restatnte para el metodo FIFO
            $table->integer('stock_restante');

            // Auditoría (opcional)
            $table->date('auditoriaFechaCreacion')->nullable();
            $table->string('auditoriaCreadoPor')->nullable();
            $table->date('auditoriaFechaModificacion')->nullable();
            $table->string('auditoriaModificadoPor')->nullable();
            $table->date('auditoriaFechaEliminacion')->nullable();
            $table->string('auditoriaEliminadoPor')->nullable();

            // Relaciones
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('restrict');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
