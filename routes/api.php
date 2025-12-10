<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Usamos la ruta completa (fully qualified name) para evitar errores de clase no encontrada.
|
*/

// ----------------------------------------------------
// 1. RUTAS PÚBLICAS (Login/Registro - RF-1, RF-2)
// ----------------------------------------------------
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

// ----------------------------------------------------
// 2. RUTAS PROTEGIDAS (Requieren el token JWT de Sanctum)
// ----------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación (RF-4)
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });

    // Módulo de Citas (RF-5, RF-6, RF-7, RF-8)
    Route::get('/appointments', [\App\Http\Controllers\AppointmentController::class, 'index']); // Obtener agenda
    Route::post('/appointments', [\App\Http\Controllers\AppointmentController::class, 'store']); // Crear cita (Reserva)
    Route::post('/appointments/{appointment}/confirm', [\App\Http\Controllers\AppointmentController::class, 'confirmAppointment']); // Confirmar (Tatuador)

});