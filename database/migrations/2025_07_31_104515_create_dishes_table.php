<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id()->comment('Identificador único del platillo');
            $table->string('codigo')->unique()->comment('Código único del platillo');
            $table->unsignedBigInteger('categoria_id')->comment('Clave foránea que indica a qué categoría pertenece el platillo');
            $table->string('nombre')->comment('Nombre del platillo');
            $table->text('descripcion')->nullable()->comment('Descripción del platillo');
            $table->decimal('precio', 8, 2)->comment('Precio regular del platillo');
            $table->decimal('precio_promocion', 8, 2)->nullable()->comment('Precio promocional del platillo');
            $table->boolean('activo')->default(true)->comment('Indica si el platillo está activo o no');
            $table->string('slug')->unique()->nullable()->comment('Identificador amigable para URL');

            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')->nullable()->comment('Fecha de creación del registro');
            $table->unsignedBigInteger('auditoriaCreadoPor')->nullable()->comment('Usuario que creó el registro');
            $table->dateTime('auditoriaFechaModificacion')->nullable()->comment('Fecha de la última modificación');
            $table->unsignedBigInteger('auditoriaModificadoPor')->nullable()->comment('Usuario que modificó el registro');
            $table->dateTime('auditoriaFechaEliminacion')->nullable()->comment('Fecha de eliminación lógica');
            $table->unsignedBigInteger('auditoriaEliminadoPor')->nullable()->comment('Usuario que eliminó el registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
