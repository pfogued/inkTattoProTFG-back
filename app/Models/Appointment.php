<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'client_id',
        'tattoo_artist_id',
        'scheduled_at',
        'description',
        'status',
    ];

    /**
     * Define la relación: Esta cita pertenece a un Cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Define la relación: Esta cita pertenece a un Tatuador.
     */
    public function tattooArtist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tattoo_artist_id');
    }

    /**
     * Define los casts para asegurar que scheduled_at sea un objeto DateTime.
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }
}