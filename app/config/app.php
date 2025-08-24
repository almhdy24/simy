<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Simy Framework',
        'env' => 'development',
        'debug' => true,
        'url' => 'http://localhost:8080'
    ],
    
    'providers' => [
        \Simy\App\Providers\AppServiceProvider::class,
        \Simy\App\Providers\ControllerServiceProvider::class,
        \Simy\App\Providers\RoutingServiceProvider::class
    ],
    
    'middleware' => [
        'web' => [
            // Web middleware classes
        ],
        'api' => [
            // API middleware classes
        ]
    ]
];