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
        Schema::create('products', function (Blueprint $table) {
            $table->id()->comment('Identificador único del producto');
            $table->string('codigo')->unique()->comment('Código único del producto');
            $table->string('nombre')->comment('Nombre del producto');
            $table->text('descripcion')->nullable()->comment('Descripción del producto');
            $table->decimal('precio', 10, 2)->comment('Precio normal del producto');
            $table->decimal('precio_promocion', 10, 2)->nullable()->comment('Precio de promoción del producto, si aplica');
            $table->integer('stock')->default(0)->comment('Cantidad de unidades en stock');
            $table->unsignedBigInteger('categoria_id')->comment('Identificador de la categoría del producto');

            // Auditoría
            $table->date('auditoriaFechaCreacion')->nullable()->comment('Fecha de creación (auditoría)');
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable()->comment('Usuario que creó el registro (auditoría)');
            $table->date('auditoriaFechaModificacion')->nullable()->comment('Fecha de última modificación (auditoría)');
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable()->comment('Usuario que modificó el registro (auditoría)');
            $table->date('auditoriaFechaEliminacion')->nullable()->comment('Fecha de eliminación (auditoría)');
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable()->comment('Usuario que eliminó el registro (auditoría)');

            // Si deseas controlar fechas automáticas, descomenta:
            // $table->timestamps()->comment('Fechas de creación y actualización automáticas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
