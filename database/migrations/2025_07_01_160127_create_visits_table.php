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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            // Estas dos columnas son para la relación polimórfica:
            // 'visitable_id' almacenará el ID del modelo visitado (ej. ID de categoría, ID de página)
            // 'visitable_type' almacenará el nombre de la clase del modelo (ej. 'App\Models\Category', 'App\Models\DashboardPage')
            $table->unsignedBigInteger('visitable_id');
            $table->string('visitable_type');
            $table->integer('count')->default(0); // Contador de visitas
            $table->timestamps();

            // Añadir un índice único para asegurar que solo haya una entrada por visitable
            $table->unique(['visitable_id', 'visitable_type']);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};

