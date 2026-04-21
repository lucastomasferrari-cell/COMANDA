<?php

use Modules\Core\Http\Middleware\InitializeAppLocaleMiddleware;

return [
    "enable_route_domain" => env('ENABLE_ROUTE_DOMAIN', false),
    'routes' => [
        "public" => [
            "domain" => env('PUBLIC_DOMAIN', 'localhost'),
            "namespace" => "Http\\Controllers",
            "middleware" => ['web'],
            "file" => "web.php"
        ],
        "api" => [
            "domain" => env('API_DOMAIN', 'api.localhost'),
            "prefix" => "api",
            "version" => "v1",
            "namespace" => "Http\\Controllers\\Api\\V1",
            "middleware" => [
                "checkInstalled",
                InitializeAppLocaleMiddleware::class,
                'api',
                'throttle:api',
                'auth',
            ],
            "file" => "api/v1.php"
        ],
    ]
];
