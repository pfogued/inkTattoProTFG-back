<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Esta configuración permite que el Front-end de Vue (5173) se comunique 
    | sin restricciones con el Back-end de Laravel (8000) durante el desarrollo.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'], 

    'allowed_methods' => ['*'], 

    // 🎯 CRÍTICO: Permitir cualquier origen (soluciona el bloqueo de red desde localhost:5173)
    'allowed_origins' => ['*'], 

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], 

    // CRÍTICO: Necesario para que Laravel Sanctum funcione con Axios
    'supports_credentials' => true, 
];