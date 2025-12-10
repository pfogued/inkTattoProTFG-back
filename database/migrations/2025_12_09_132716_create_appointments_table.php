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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // 1. Relación con el Cliente (Quien reserva la cita)
            $table->foreignId('client_id')->constrained('users');

            // 2. Relación con el Tatuador (Con quien se reserva la cita)
            // Se debe asegurar que esta columna solo acepta usuarios con role_id = 2.
            $table->foreignId('tattoo_artist_id')->constrained('users');

            // 3. Información de la Cita
            $table->dateTime('scheduled_at'); // Fecha y hora programada
            $table->text('description'); // Detalles de lo que se tatuará
            
            // 4. Estado (RF-5)
            $table->enum('status', ['pending', 'approved', 'canceled'])->default('pending');

            $table->timestamps();
        });
    }

    // ... (resto del método down)
};