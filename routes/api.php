<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;        
use App\Http\Controllers\AppointmentController; 
use App\Http\Controllers\DesignController;     
use App\Http\Controllers\ChatController;      
use App\Http\Controllers\PaymentController;    

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ----------------------------------------------------
// 1. RUTAS PÚBLICAS (Sin Autenticación)
// ----------------------------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('tattoo-artists', [AppointmentController::class, 'getTattooArtists']);

// ----------------------------------------------------
// 2. RUTAS PROTEGIDAS (Requieren el token JWT de Sanctum)
// ----------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación (RF-4)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::get('users', [AuthController::class, 'getAllUsers']);

    // Módulo de Citas (RF-3, RF-5, RF-6, RF-7)
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']); 
    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirmAppointment']); 
    
    // RF-7: Modificación y Cancelación
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']); 
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancelAppointment']); 

    // Módulos de Diseños, Chat, y Pagos
    Route::get('clients/associated', [AppointmentController::class, 'getAssociatedClients']); 
    Route::resource('designs', DesignController::class)->only(['index', 'store', 'destroy']); 
    Route::patch('designs/{design}/annotation', [DesignController::class, 'updateAnnotation']);
    Route::get('chat/{user}', [ChatController::class, 'getChat']); 
    Route::post('chat/{chat}', [ChatController::class, 'sendMessage']); 
    Route::get('/payments', [PaymentController::class, 'index']); 

});