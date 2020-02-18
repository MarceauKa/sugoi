<?php

// Ne modifiez pas ce fichier, copier le fichier .env.example en .env
return [
    'name' => env('APP_NAME', 'Sugoi'),
    'debug' => env('APP_DEBUG', true),
    'db' => [
        'host' => env('DB_HOST', 'localhost'),
        'name' => env('DB_NAME', 'blog'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
        'port' => env('DB_PORT', 3306),
    ],
];