<?php
// ... (resto del namespace y uses)

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
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            
            // 1. Relación con el Tatuador (Quien sube el diseño)
            // Solo los usuarios con role_id = 2 deberían subir diseños.
            $table->foreignId('tattoo_artist_id')->constrained('users');

            // 2. Contenido del Diseño
            $table->string('title'); // Título del diseño
            $table->text('description')->nullable(); // Descripción, tags, etc.
            $table->string('image_url'); // URL o path al archivo de la imagen (RF-8)
            $table->enum('style', ['traditional', 'watercolor', 'blackwork', 'geometric', 'other'])->default('other'); // Estilo para filtros

            $table->timestamps();
        });
    }

    // ... (resto del método down)
};