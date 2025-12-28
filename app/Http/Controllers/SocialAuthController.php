<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    // Redirige al usuario a Google
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    // Recibe los datos de Google tras el login
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // Buscamos si el email ya existe en nuestra DB
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // Si es nuevo, lo registramos (Rol 1 = Cliente)
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'role_id' => 1, 
                    'password' => null, 
                ]);
            } else {
                // Si ya existe, vinculamos su ID de Google
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }

            // Creamos el token de acceso (Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirigimos al Frontend pasando el token por la URL
            return redirect('http://localhost:5173/social-callback?token=' . $token);

        } catch (\Exception $e) {
            return redirect('http://localhost:5173/login?error=social_auth_failed');
        }
    }
}