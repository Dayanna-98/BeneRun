<?php

$defaultOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:5174',
    'http://127.0.0.1:5174',
];

$configuredOrigins = array_filter(array_map(
    static fn (string $origin): string => trim($origin),
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', implode(',', $defaultOrigins)))
));

$configuredOriginPatterns = array_filter(array_map(
    static fn (string $pattern): string => trim($pattern),
    explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))
));

return [
    'paths' => [
        'api/*',
        'broadcasting/auth',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => $configuredOrigins,

    'allowed_origins_patterns' => $configuredOriginPatterns,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
