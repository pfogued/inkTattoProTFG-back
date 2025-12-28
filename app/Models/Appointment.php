<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    /**
     * Atributos asignables masivamente.
     * El 'status' es clave aquí para que el controlador pueda cambiarlo a 'approved' tras el pago.
     */
    protected $fillable = [
        'client_id',
        'tattoo_artist_id',
        'scheduled_at',
        'description',
        'status', // 'pending', 'approved', 'canceled'
    ];

    /**
     * Relación: Una cita pertenece a un Cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relación: Una cita pertenece a un Tatuador.
     */
    public function tattooArtist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tattoo_artist_id');
    }

    /**
     * NUEVA RELACIÓN: Una cita puede tener varios pagos (aunque sea un depósito).
     * Esto ayuda a que el Tatuador pueda rastrear los pagos desde la cita.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Formateo automático de la fecha.
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }
}