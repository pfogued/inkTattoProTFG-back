<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;        
use App\Http\Controllers\AppointmentController; 
use App\Http\Controllers\DesignController;     

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ----------------------------------------------------
// 1. RUTAS PÚBLICAS (Login/Registro - RF-1, RF-2)
// ----------------------------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// RUTA CRÍTICA NUEVA: Obtener lista de Tatuadores para el selector de reserva
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
    
    // RUTA PARA MODAL DE DISEÑOS: Obtener clientes asociados al Tatuador
    Route::get('clients/associated', [AppointmentController::class, 'getAssociatedClients']); 

    // Módulo de Diseños (RF-8, RF-9, RF-10)
    Route::resource('designs', DesignController::class)->only(['index', 'store', 'destroy']); 
    Route::patch('designs/{design}/annotation', [DesignController::class, 'updateAnnotation']);

});