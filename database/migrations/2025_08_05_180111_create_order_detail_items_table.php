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
        Schema::create('order_detail_items', function (Blueprint $table) {
            $table->id()->comment('Identificador único del detalle de pedido');
            $table->unsignedBigInteger('restaurant_order_id')->comment('ID del pedido al que pertenece');
            $table->unsignedBigInteger('dish_id')->comment('ID del platillo');
            $table->integer('cantidad')->default(1)->comment('Cantidad solicitada');
            $table->decimal('precio_unitario', 8, 2)->comment('Precio unitario del platillo en el momento del pedido');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal calculado');

            // Foreign keys
            $table->foreign('restaurant_order_id')
                ->references('id')->on('restaurant_orders')
                ->onDelete('cascade');

            $table->foreign('dish_id')
                ->references('id')->on('dishes')
                ->onDelete('restrict');
            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')->nullable()->comment('Fecha de creación del registro');
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable()->comment('Usuario que creó el registro');
            $table->dateTime('auditoriaFechaModificacion')->nullable()->comment('Fecha de la última modificación');
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable()->comment('Usuario que modificó el registro');
            $table->dateTime('auditoriaFechaEliminacion')->nullable()->comment('Fecha de eliminación lógica');
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable()->comment('Usuario que eliminó el registro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detail_items');
    }
};
