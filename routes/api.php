<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;        
use App\Http\Controllers\AppointmentController; 
use App\Http\Controllers\DesignController;     
use App\Http\Controllers\ChatController;      // <-- NUEVA IMPORTACIÓN

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Este archivo define las rutas para tu aplicación API.
|
*/

// ----------------------------------------------------
// 1. RUTAS PÚBLICAS (Sin Autenticación)
// ----------------------------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Obtener lista de Tatuadores para el selector de reserva (PÚBLICO)
Route::get('tattoo-artists', [AppointmentController::class, 'getTattooArtists']);

// ----------------------------------------------------
// 2. RUTAS PROTEGIDAS (Requieren el token JWT de Sanctum)
// ----------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación (RF-4)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });

    // Módulo de Citas (RF-3, RF-5, RF-6)
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']); 
    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirmAppointment']); 
    
    // Obtener clientes asociados al Tatuador (para modal de diseños)
    Route::get('clients/associated', [AppointmentController::class, 'getAssociatedClients']); 

    // Módulo de Diseños (RF-8, RF-9, RF-10)
    Route::resource('designs', DesignController::class)->only(['index', 'store', 'destroy']); 
    Route::patch('designs/{design}/annotation', [DesignController::class, 'updateAnnotation']);

    // Módulo de Mensajería (RF-11, RF-12) <-- NUEVAS RUTAS
    // GET /api/chat/{user}: Obtener o crear chat con un usuario
    Route::get('chat/{user}', [ChatController::class, 'getChat']); 
    // POST /api/chat/{chat}: Enviar mensaje al chat existente
    Route::post('chat/{chat}', [ChatController::class, 'sendMessage']); 

    // Obtener lista de todos los usuarios (para el chat)
    Route::get('users', [AuthController::class, 'getAllUsers']);

    // Módulo de Pagos (RF-13)
    Route::get('/payments', [PaymentController::class, 'index']);
});