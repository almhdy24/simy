<?php
use Simy\Core\Application;
use Simy\Core\Response;
use Simy\Core\Routing\RouteRegistrar;

/** @var RouteRegistrar $route */
$route = Application::getInstance()->getContainer()->get(RouteRegistrar::class);

// API versioning group
$route->group('/api/v1', function($route) {
    // Users resource
    $route->group('/users', function($route) {
        $route->get('/', [\Simy\App\Controllers\Api\UserController::class, 'index']);
        $route->post('/', [\Simy\App\Controllers\Api\UserController::class, 'store']);
        $route->get('/{id}', [\Simy\App\Controllers\Api\UserController::class, 'show']);
        $route->put('/{id}', [\Simy\App\Controllers\Api\UserController::class, 'update']);
        $route->delete('/{id}', [\Simy\App\Controllers\Api\UserController::class, 'destroy']);
    }, ['api', 'throttle']);

    // Posts resource
    $route->group('/posts', function($route) {
        $route->get('/', [\Simy\App\Controllers\Api\PostController::class, 'index']);
        $route->post('/', [\Simy\App\Controllers\Api\PostController::class, 'store']);
    }, ['api', 'throttle']);

    // Authentication routes
    $route->post('/login', [\Simy\App\Controllers\Api\AuthController::class, 'login']);
    $route->post('/register', [\Simy\App\Controllers\Api\AuthController::class, 'register']);
}, ['api']);