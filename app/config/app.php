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
        \App\Providers\AppServiceProvider::class
    ],
    
    'middleware' => [
        'web' => [],
        'api' => []
    ]
];