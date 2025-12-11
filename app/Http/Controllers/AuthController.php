<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // CRÍTICO para Login y Hash manual

class AuthController extends Controller
{
    /**
     * RF-1: Registro de un nuevo usuario (Cliente o Tatuador).
     * SOLUCIÓN CRÍTICA: Hash manual para asegurar la compatibilidad.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|integer|in:1,2', // 1=Cliente, 2=Tatuador
        ]);

        // 🎯 SOLUCIÓN FINAL: HASH MANUAL
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // <-- ¡FORZAMOS EL HASH AQUÍ!
            'role_id' => $request->role_id,
        ]);

        // El token se crea correctamente
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

        // 1. Buscar al usuario por email
        $user = User::where('email', $credentials['email'])->first();

        // 2. Comprobación de la contraseña contra el hash de la BD
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }
        
        // 3. Si la comprobación es exitosa, se crea el token
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
}