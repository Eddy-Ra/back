<?php

return [
    'paths' => ['*', 'api/*', 'api/reponses/*', 'api/login', 'api/register', 'api/auth/google*', 'api/contacts/*', 'api/contacts', 'api/sync-to-supabase', 'api/test-supabase', 'api/categories', 'api/source-stats', 'auth/*', 'sanctum/csrf-cookie', 'api/email/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:8080',  // Ajouté
        'http://127.0.0.1:8080',  // Ajouté
        'https://autoprospectionadmin.omega-connect.tech',
        'https://www.autoprospectionadmin.omega-connect.tech',
        'https://autoprospection.vercel.app'
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
