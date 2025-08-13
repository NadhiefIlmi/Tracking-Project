<?php

return [
    'paths' => ['api/*', 'aplikasi','apps/{id}/detail', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // atau ['http://localhost:5173'] untuk lebih spesifik
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];