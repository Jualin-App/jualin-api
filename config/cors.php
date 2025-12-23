<?php

return [
    'paths' => ['api/*', 'v1/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://localhost:3001,http://192.168.0.101:3000,https://helaine-cleanlier-noncalculably.ngrok-free.dev,https://www.jualin-tel.biz.id')),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
