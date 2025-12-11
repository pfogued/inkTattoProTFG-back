<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;        // Importar AuthController
use App\Http\Controllers\AppointmentController; // Importar AppointmentController
use App\Http\Controllers\DesignController;      // <-- Importación CRÍTICA

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
// Nota: Utilizamos la sintaxis 'Clase::class' para mayor claridad y modernidad
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ----------------------------------------------------
// 2. RUTAS PROTEGIDAS (Requieren el token JWT de Sanctum)
// ----------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación (RF-4)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });

    // Módulo de Citas (RF-3, RF-5)
    Route::get('/appointments', [AppointmentController::class, 'index']); // Obtener agenda (Cliente/Tatuador)
    Route::post('/appointments', [AppointmentController::class, 'store']); // Crear cita (Reserva por Cliente)
    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirmAppointment']); // Confirmar (Tatuador)
    // NOTA: Se asume que el método 'store' de AppointmentController también gestiona RF-3.

    // Módulo de Diseños (RF-8, RF-9)
    // RF-9 (index): Ver todos los diseños.
    // RF-8 (store): Subir un nuevo diseño (solo Tatuador).
    // Usamos Route::resource para crear GET /designs y POST /designs automáticamente.
    Route::resource('designs', DesignController::class)->only(['index', 'store']); 

});