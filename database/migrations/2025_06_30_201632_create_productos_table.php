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
        Schema::create('productos', function (Blueprint $table) { // Cambiado a 'productos'
            $table->id(); // Laravel convention: uses 'id' as primary key by default
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable(); // Descripción puede ser opcional
            $table->decimal('precio_unitario', 10, 2);
            $table->integer('stock_actual')->default(0);
            // Asegúrate de que la tabla 'categorias' exista y tenga una columna 'id'
            $table->foreignId('id_categoria')->constrained('categorias')->onDelete('cascade'); // Referencia a 'id' de la tabla 'categorias'
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
