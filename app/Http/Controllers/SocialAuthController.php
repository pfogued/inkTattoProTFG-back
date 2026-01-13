<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación del proveedor (Google).
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Recibe la respuesta del proveedor tras la autenticación.
     */
    public function handleProviderCallback($provider)
    {
        try {
            // Obtenemos los datos del usuario desde Google
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // Buscamos si el email ya existe en nuestra base de datos
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // Si el usuario no existe, lo creamos (Rol 1 = Cliente por defecto)
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'role_id' => 1, 
                    'password' => null, // No necesita password al ser login social
                ]);
            } else {
                // Si ya existe, actualizamos sus datos de proveedor por seguridad
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }

            // Generamos el token de acceso mediante Laravel Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // IMPORTANTE: Obtenemos la URL del Frontend desde las variables de entorno.
            // Si no existe, por defecto usará localhost para que no te falle en local.
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

            // Redirigimos al Frontend inyectando el token en la ruta de callback
            return redirect($frontendUrl . '/social-callback?token=' . $token);

        } catch (\Exception $e) {
            // Logueamos el error por si necesitas revisarlo en Railway (View Logs)
            Log::error("Error en login social con {$provider}: " . $e->getMessage());

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect($frontendUrl . '/login?error=social_auth_failed');
        }
    }
}