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
        Schema::table('designs', function (Blueprint $table) {
            // Bandera de privacidad
            $table->boolean('is_private')->default(false)->after('tattoo_artist_id');

            // Columna opcional para asociar a un Cliente específico (si es privado)
            $table->foreignId('client_id')
                  ->nullable() 
                  ->constrained('users')
                  ->after('is_private');
            
            // Columna para la anotación/comentario del cliente (RF-10)
            $table->text('client_annotation')->nullable()->after('style');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['is_private', 'client_id', 'client_annotation']);
        });
    }
};