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
            $table->id();
            $table->string('codigo')->unique(); // Código único del producto
            $table->string('nombre'); // Nombre del producto
            $table->text('descripcion')->nullable(); // Descripción del producto
            $table->decimal('precio', 10, 2); // Precio del producto
            $table->integer('stock')->default(0); // Stock del producto
            $table->unsignedBigInteger('categoria_id'); // Categoria del producto (Relación lógica)

            // Auditoría
            $table->date('auditoriaFechaCreacion')->nullable(); // LocalDate auditoriaFechaCreacion
            $table->string('auditoriaCreadoPor')->nullable(); // String auditoriaCreadoPor
            $table->date('auditoriaFechaModificacion')->nullable(); // LocalDate auditoriaFechaModificacion
            $table->string('auditoriaModificadoPor')->nullable(); // String auditoriaModificadoPor
            $table->date('auditoriaFechaEliminacion')->nullable(); // LocalDate auditoriaFechaEliminacion
            $table->string('auditoriaEliminadoPor')->nullable(); // String auditoriaEliminadoPor
            // $table->timestamps(); // Si deseas controlar fechas de creación/actualización
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
