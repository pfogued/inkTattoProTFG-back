<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user1_id',
        'user2_id',
    ];

    /**
     * Un chat tiene muchos mensajes.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
    
    // Relación para obtener al Participante 1
    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    // Relación para obtener al Participante 2
    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }
}