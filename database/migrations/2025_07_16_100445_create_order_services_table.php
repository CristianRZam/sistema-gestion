<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_services', function (Blueprint $table) {
            $table->id()->comment('ID de la orden de servicio');

            $table->unsignedBigInteger('customer_id')->nullable();
            // Relación lógica con reservation_room (sin FK)
            $table->unsignedBigInteger('reservation_room_id')->nullable()->comment('ID del detalle de habitación asociado (relación lógica)');

            $table->dateTime('fecha')->comment('Fecha de la orden')->nullable();
            $table->decimal('descuento', 10, 2)->default(0)->comment('Total de la orden');
            $table->decimal('total', 10, 2)->default(0)->comment('Total de la orden');
            $table->unsignedBigInteger('estado_id')->default(1)->comment("Estado: pendiente, completado, cancelado");
            $table->boolean('pagado')->default(false)->comment('Indica si ha sido pagada');
            // Usuario (vendedor) que realizó la venta (relación lógica, sin clave foránea)
            $table->unsignedBigInteger('usuario_id')->nullable();

            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')->nullable()->comment('Fecha de creación del registro');
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable()->comment('Usuario que creó el registro');
            $table->dateTime('auditoriaFechaModificacion')->nullable()->comment('Fecha de la última modificación');
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable()->comment('Usuario que modificó el registro');
            $table->dateTime('auditoriaFechaEliminacion')->nullable()->comment('Fecha de eliminación lógica');
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable()->comment('Usuario que eliminó el registro');

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('order_services');
    }
};
