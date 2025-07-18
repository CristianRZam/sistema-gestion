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
        Schema::create('detail_order_services', function (Blueprint $table) {
            $table->id()->comment('ID del detalle de orden de servicio');

            $table->unsignedBigInteger('order_service_id')->comment('ID de la orden de servicio');
            $table->unsignedBigInteger('service_id')->comment('ID del servicio solicitado');

            $table->integer('cantidad')->default(1)->comment('Cantidad solicitada');
            $table->decimal('precio_unitario', 10, 2)->comment('Precio unitario del servicio');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal calculado');

            // Foreign keys
            $table->foreign('order_service_id')
                ->references('id')->on('order_services')
                ->onDelete('cascade');

            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('restrict');

            // Auditoría
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
        Schema::dropIfExists('detail_order_services');
    }
};
