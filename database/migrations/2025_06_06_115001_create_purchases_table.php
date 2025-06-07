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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            // Cliente asociado a la venta
            $table->unsignedBigInteger('supplier_id')->nullable();

            // Usuario (vendedor) que realizó la venta (relación lógica, sin clave foránea)
            $table->unsignedBigInteger('usuario_id')->nullable();

            // Fecha de la venta
            $table->dateTime('fecha_compra');

            // Total de la venta
            $table->decimal('total', 10, 2);

            // Descuento de la venta
            $table->decimal('descuento', 10, 2)->default(0);

            // Método de pago (opcional)
            $table->unsignedBigInteger('metodo_pago_id')->nullable();

            // Estado de la venta (ej. completada, anulada)
            $table->unsignedBigInteger('estado_compra_id')->nullable();

            // Auditoría
            $table->date('auditoriaFechaCreacion')->nullable();
            $table->string('auditoriaCreadoPor')->nullable();
            $table->date('auditoriaFechaModificacion')->nullable();
            $table->string('auditoriaModificadoPor')->nullable();
            $table->date('auditoriaFechaEliminacion')->nullable();
            $table->string('auditoriaEliminadoPor')->nullable();

            // Claves foráneas lógicas
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
