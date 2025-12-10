<?php

namespace App\Http\Controllers; 

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * RF-1: Registrar nuevo usuario (Cliente o Tatuador)
     */
    public function register(Request $request)
    {
        // RF-14: Validación de campos obligatorios
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|integer|in:1,2', // Aseguramos que el rol sea 1 o 2
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        // Creación del token de sesión (Requiere la tabla personal_access_tokens correcta)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ]
        ], 201);
    }

    /**
     * RF-2: Iniciar sesión
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if (!Auth::attempt($credentials)) {
            // RF-2: Mensaje de error para credenciales incorrectas
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas. Verifique su correo o contraseña.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ]
        ]);
    }

    /**
     * RF-4: Cerrar sesión
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete(); 
        return response()->json(['message' => 'Sesión cerrada con éxito.'], 200);
    }
}