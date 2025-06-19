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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental

            // Clave foránea al producto, con eliminación en cascada
            $table->foreignId('product_id')
                ->constrained('products') // Se asocia a la tabla 'products'
                ->onDelete('cascade');    // Si se elimina el producto, se eliminan sus imágenes

            $table->string('imagen_url'); // Ruta o URL de la imagen del producto

            $table->boolean('es_principal')->default(false); // Indica si es la imagen principal

            // Campos de auditoría (opcional, si no se usan timestamps automáticos)
            $table->date('auditoriaFechaCreacion')->nullable();      // Fecha de creación del registro
            $table->string('auditoriaCreadoPor')->nullable();        // Usuario que creó el registro
            $table->date('auditoriaFechaModificacion')->nullable();  // Fecha de última modificación
            $table->string('auditoriaModificadoPor')->nullable();    // Usuario que modificó por última vez
            $table->date('auditoriaFechaEliminacion')->nullable();   // Fecha de eliminación lógica
            $table->string('auditoriaEliminadoPor')->nullable();     // Usuario que eliminó el registro
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Elimina la tabla si existe, permitiendo reversión de la migración
        Schema::dropIfExists('product_images');
    }
};
