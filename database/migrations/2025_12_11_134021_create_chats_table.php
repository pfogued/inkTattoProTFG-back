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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            
            // Participante 1
            $table->foreignId('user1_id')->constrained('users');
            
            // Participante 2
            $table->foreignId('user2_id')->constrained('users');

            // Asegurar que no haya duplicados (Ej: Chat entre 1 y 2 es igual que 2 y 1)
            $table->unique(['user1_id', 'user2_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};