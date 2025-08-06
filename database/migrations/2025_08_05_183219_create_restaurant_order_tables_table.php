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
        Schema::create('restaurant_order_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_order_id')->comment('Orden asociada');
            $table->unsignedBigInteger('restaurant_table_id')->comment('ID de la mesa');

            $table->foreign('restaurant_table_id')
                ->references('id')->on('restaurant_tables')
                ->onDelete('restrict');
            $table->foreign('restaurant_order_id')
                ->references('id')->on('restaurant_orders')
                ->onDelete('cascade');

            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')->nullable();
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable();
            $table->dateTime('auditoriaFechaModificacion')->nullable();
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable();
            $table->dateTime('auditoriaFechaEliminacion')->nullable();
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_order_tables');
    }
};
