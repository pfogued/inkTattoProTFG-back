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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Relación con el chat al que pertenece el mensaje
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            
            // Quién envió el mensaje
            $table->foreignId('sender_id')->constrained('users');
            
            // Contenido del mensaje
            $table->text('content');
            
            // Marca para saber si el receptor ha leído el mensaje
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};