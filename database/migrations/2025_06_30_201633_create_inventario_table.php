<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_movimiento'); // Clave primaria para el movimiento de inventario
            // Clave foránea que referencia la columna 'id' de la tabla 'productos'
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade');
            $table->string('tipo', 10); // 'ingreso' o 'salida'
            $table->integer('cantidad')->unsigned(); // Cantidad del movimiento, siempre positiva
            $table->timestamp('fecha')->useCurrent(); // Fecha y hora del movimiento, por defecto la actual
            $table->text('observacion')->nullable(); // Observaciones adicionales
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};

