<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- IMPORTACIÓN NECESARIA

class User extends Authenticatable
{
    // USO DEL TRAIT: Necesario para que el AuthController pueda usar createToken()
    use HasApiTokens, HasFactory, Notifiable; 

    /**
     * Los atributos que se pueden asignar masivamente (CRUCIAL).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // <-- CAMPO CRÍTICO
    ];

    /**
     * Los atributos que deberían estar ocultos para la serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define el tipo de datos de los atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Asegura el hashing de la contraseña
        ];
    }
    
    // --- LÓGICA ADICIONAL ---
    
    // Método de verificación de rol (Usado en el Front-end)
    public function isTattooArtist()
    {
        return $this->role_id === 2;
    }
}