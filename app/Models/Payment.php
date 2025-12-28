<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'client_id',      
        'appointment_id', 
        'amount',
        'stripe_id',    // <-- ESTE FALTABA: Sin él, Laravel da error 500 al guardar
        'type',
        'status',
    ];

    /**
     * Define la relación: Un pago pertenece a un cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Define la relación: Un pago opcionalmente pertenece a una cita.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}