<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'customers' (clientes).
 * Esta tabla almacena la información básica de los clientes
 * como nombre, documento de identidad, teléfono, email y dirección.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración: crea la tabla 'customers'.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            // ID autoincremental como clave primaria
            $table->id();

            // Nombre completo del cliente
            $table->string('nombre');

            // Documento de identidad o RUC (único)
            $table->string('documento')->unique();

            // Teléfono del cliente (opcional)
            $table->string('telefono')->nullable();

            // Correo electrónico (único y opcional)
            $table->string('email')->nullable()->unique();

            // Dirección del cliente (opcional)
            $table->string('direccion')->nullable();

            // Campos personalizados de auditoría
            // Fecha de creación
            $table->date('auditoriaFechaCreacion')->nullable();

            // Usuario que creó el registro
            $table->string('auditoriaCreadoPor')->nullable();

            // Fecha de última modificación
            $table->date('auditoriaFechaModificacion')->nullable();

            // Usuario que modificó
            $table->string('auditoriaModificadoPor')->nullable();

            // Fecha de eliminación lógica
            $table->date('auditoriaFechaEliminacion')->nullable();

            // Usuario que elimino
            $table->string('auditoriaEliminadoPor')->nullable();
            // Campos created_at y updated_at
            //$table->timestamps();
        });
    }

    /**
     * Revierte la migración: elimina la tabla 'customers'.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
