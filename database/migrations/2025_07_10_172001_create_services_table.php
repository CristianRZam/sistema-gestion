<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración: crea la tabla 'services'.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id()->comment('Identificador único del servicio');;  // ID único del servicio

            $table->string('nombre')
            ->comment('Nombre del servicio');

            $table->text('descripcion')
            ->nullable()
                ->comment('Descripción detallada del servicio (opcional)');

            $table->decimal('precio', 10, 2)
            ->comment('Precio unitario del servicio');
            $table->boolean('activo')->default(true)->comment('Define si el servicio esta disponible');

            // Auditoría
            $table->dateTime('auditoriaFechaCreacion')
                ->nullable()
                ->comment('Fecha de creación del registro');

            $table->unsignedBigInteger('auditoriaCreadoPor')
                ->nullable()
                ->comment('Usuario que creó el registro');

            $table->dateTime('auditoriaFechaModificacion')
                ->nullable()
                ->comment('Fecha de la última modificación');

            $table->unsignedBigInteger('auditoriaModificadoPor')
                ->nullable()
                ->comment('Usuario que modificó el registro');

            $table->dateTime('auditoriaFechaEliminacion')
                ->nullable()
                ->comment('Fecha de eliminación lógica');

            $table->unsignedBigInteger('auditoriaEliminadoPor')
                ->nullable()
                ->comment('Usuario que eliminó el registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
