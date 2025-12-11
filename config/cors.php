<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar cómo las cabeceras CORS son respondidas por tu aplicación.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // CRÍTICO: Debemos permitir el acceso desde el Front-end de Vue.
    // Incluir localhost y 127.0.0.1 para cubrir todas las configuraciones de navegador/Vite.
    'allowed_origins' => [
        'http://localhost:5173', 
        'http://127.0.0.1:5173'
    ],

    'allowed_methods' => ['*'], // Permite todos los métodos (GET, POST, PUT, DELETE, PATCH, OPTIONS)

    'allowed_headers' => ['*'], // Permite todas las cabeceras enviadas

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];