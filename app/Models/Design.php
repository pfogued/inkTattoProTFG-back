<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Design extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'tattoo_artist_id',
        'client_id',          // <-- NUEVO: Cliente asociado si es privado
        'is_private',         // <-- NUEVO: Bandera de privacidad
        'title',
        'description',
        'image_url',
        'style',
        'client_annotation',  // <-- Nuevo: Para comentarios del cliente (RF-10)
    ];

    /**
     * Define la relación: Este diseño pertenece a un Tatuador.
     */
    public function tattooArtist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tattoo_artist_id')->select(['id', 'name', 'email']);
    }
    
    /**
     * Define la relación: Este diseño opcionalmente pertenece a un Cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->select(['id', 'name', 'email']);
    }

    /**
     * Define los casts para los tipos de datos.
     */
    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }
}