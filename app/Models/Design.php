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
        'title',
        'description',
        'image_url',
        'style',
    ];

    /**
     * Define la relación: Este diseño pertenece a un Tatuador.
     */
    public function tattooArtist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tattoo_artist_id');
    }
}