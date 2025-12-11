<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 

class AuthController extends Controller
{
    /**
     * RF-1: Registro de un nuevo usuario (Cliente o Tatuador).
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|integer|in:1,2', // 1=Cliente, 2=Tatuador
        ]);

        // Aseguramos el hash manual para prevenir inconsistencias.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'role_id' => $request->role_id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'message' => 'Registro exitoso'
        ], 201);
    }

    /**
     * RF-2: Iniciar sesión del usuario con comprobación manual de hash.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Comprobación de la contraseña contra el hash de la BD
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }
        
        $user->tokens()->delete(); 
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'message' => 'Login exitoso'
        ]);
    }

    /**
     * RF-4: Cierra la sesión del usuario (revoca el token actual).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada con éxito']);
    }

    /**
     * Devuelve todos los usuarios del sistema (excepto el propio usuario).
     * Necesario para que la lista de contactos del chat funcione.
     */
   public function getAllUsers()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $query = User::where('id', '!=', $currentUser->id);

        // Lógica de restricción de contactos (RF-11/RF-12)
        if ($currentUser->role_id === 1) {
            // Si es Cliente (role_id=1): Solo ve Tatuadores (role_id=2)
            $query->where('role_id', 2);
        }
        // Si es Tatuador (role_id=2), ve a todos los demás (Clientes y Tatuadores), 
        // lo cual ya está implícito con where('id', '!=', $currentUser->id).

        $users = $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'role_id' => $user->role_id,
                'hasNewMessages' => false,
            ];
        });

        return response()->json(['users' => $users]);
    }
}
