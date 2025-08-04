<?php
return [
  'app' => [
    'debug' => true,
    'env' => 'development',
    'error_log' => __DIR__ . '/../../storage/logs/error.log',
    'providers' => [
      App\Providers\DatabaseServiceProvider::class,
      App\Providers\TemplateServiceProvider::class,
      App\Providers\AppServiceProvider::class,
    ],
  ],
  'http' => [
    'default_headers' => [
      'Content-Type' => 'text/html; charset=utf-8'
    ]
  ],

];