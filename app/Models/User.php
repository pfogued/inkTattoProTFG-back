<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // Para distinguir Cliente (1) de Tatuador (2)
        'provider',    
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * RELACIÓN: Citas como cliente.
     * Esto permite al sistema saber qué citas tiene asociadas este usuario.
     */
    public function appointmentsAsClient()
    {
        // Conecta el ID del usuario con la columna 'client_id' de la tabla 'appointments'
        return $this->hasMany(Appointment::class, 'client_id');
    }
}