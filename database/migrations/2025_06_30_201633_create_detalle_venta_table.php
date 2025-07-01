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
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            // Clave foránea que referencia la columna 'id' de la tabla 'ventas'
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            // Clave foránea que referencia la columna 'id' de la tabla 'productos'
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad'); // Cantidad de este producto en la venta
            $table->decimal('precio_unitario', 10, 2); // Precio al que se vendió el producto
            $table->decimal('subtotal', 10, 2); // Subtotal por este producto (cantidad * precio_unitario)
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};

