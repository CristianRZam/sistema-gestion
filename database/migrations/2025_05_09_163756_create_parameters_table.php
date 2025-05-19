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
        Schema::create('parameters', function (Blueprint $table) {
            $table->id(); // Long id
            $table->unsignedBigInteger('idParametroPadre')->nullable(); // Long idParametroPadre
            $table->unsignedBigInteger('idParametro')->nullable(); // Long idParametro
            $table->string('tipo'); // String tipo
            $table->string('nombre'); // String nombre
            $table->string('nombreCorto')->nullable(); // String nombreCorto
            $table->unsignedBigInteger('orden')->nullable(); // Long orden

            // Auditoría
            $table->date('auditoriaFechaCreacion')->nullable(); // LocalDate auditoriaFechaCreacion
            $table->string('auditoriaCreadoPor')->nullable(); // String auditoriaCreadoPor
            $table->date('auditoriaFechaModificacion')->nullable(); // LocalDate auditoriaFechaModificacion
            $table->string('auditoriaModificadoPor')->nullable(); // String auditoriaModificadoPor
            $table->date('auditoriaFechaEliminacion')->nullable(); // LocalDate auditoriaFechaEliminacion
            $table->string('auditoriaEliminadoPor')->nullable(); // String auditoriaEliminadoPor

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameters');
    }
};
