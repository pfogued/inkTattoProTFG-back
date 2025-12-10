<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra cualquier servicio de aplicación.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializa cualquier servicio de aplicación.
     * * Este método es CRÍTICO en Laravel 12 para la carga explícita de rutas de la API,
     * ya que RouteServiceProvider ya no está presente por defecto.
     */
    public function boot(): void
    {
        // 🎯 SOLUCIÓN AL ERROR 405: Forzar la carga de la ruta API con prefijo.
        Route::prefix('api')
            ->middleware('api') // Asigna el middleware 'api' (Sanctum, CORS, etc.)
            ->group(base_path('routes/api.php')); // Carga el archivo api.php
    }
}