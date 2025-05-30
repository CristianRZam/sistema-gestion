<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'suppliers' (proveedores).
 * Esta tabla almacena la información básica de los proveedores
 * como nombre, RUC, teléfono, email y dirección, junto con campos de auditoría.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración: crea la tabla 'suppliers'.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            // ID autoincremental como clave primaria
            $table->id();

            // Nombre del proveedor
            $table->string('nombre');

            // RUC del proveedor (único)
            $table->string('documento')->unique();

            // Teléfono del proveedor (opcional)
            $table->string('telefono')->nullable();

            // Correo electrónico del proveedor (único y opcional)
            $table->string('email')->nullable()->unique();

            // Dirección del proveedor (opcional)
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

            // Usuario que eliminó lógicamente
            $table->string('auditoriaEliminadoPor')->nullable();

            // Campos created_at y updated_at deshabilitados
            // $table->timestamps();
        });
    }

    /**
     * Revierte la migración: elimina la tabla 'suppliers'.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
