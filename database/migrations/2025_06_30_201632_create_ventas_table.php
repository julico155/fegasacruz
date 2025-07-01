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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental (id de la venta)
            // Clave foránea que referencia la tabla 'users' (asumiendo que los clientes son usuarios)
            // Si tienes una tabla 'clientes' separada, cambia 'users' por 'clientes'
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_venta')->useCurrent(); // Fecha y hora de la venta
            $table->decimal('total', 10, 2); // Monto total de la venta
            $table->string('estado')->default('pendiente'); // Estado de la venta (ej: pendiente, completado, cancelado)
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};

