<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Esta función añade todas las columnas necesarias a la tabla 'payments'
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // 1. Añadir client_id (quién paga)
            if (!Schema::hasColumn('payments', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('users')->onDelete('cascade');
            }

            // 2. Añadir appointment_id (qué cita se paga)
            if (!Schema::hasColumn('payments', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
            }

            // 3. Añadir amount (cantidad de dinero)
            if (!Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 8, 2)->default(0);
            }

            // 4. Añadir stripe_id (el identificador que nos da Stripe)
            if (!Schema::hasColumn('payments', 'stripe_id')) {
                $table->string('stripe_id')->nullable();
            }

            // 5. Añadir status (estado del pago: completed, pending...)
            if (!Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('pending');
            }

            // 6. Añadir type (tipo de pago: depósito, total...)
            if (!Schema::hasColumn('payments', 'type')) {
                $table->string('type')->default('deposit');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Esta función permite borrar las columnas si algo sale mal.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Eliminamos las columnas y las claves foráneas
            $table->dropForeign(['payments_client_id_foreign']);
            $table->dropForeign(['payments_appointment_id_foreign']);
            $table->dropColumn(['client_id', 'appointment_id', 'amount', 'stripe_id', 'status', 'type']);
        });
    }
};