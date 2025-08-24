<?php
use Simy\Core\Application;
use Simy\Core\Response;
use Simy\Core\Routing\RouteRegistrar;

/** @var RouteRegistrar $route */
$route = Application::getInstance()->getContainer()->get(RouteRegistrar::class);

// API Routes
$route->group('/api/v1', function($route) {
    
    // Health check
    $route->get('/health', fn() => Response::json(['status' => 'ok', 'service' => 'Simy']));
    
    // Simple API endpoints
    $route->get('/users', fn() => Response::json([
        'users' => [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ]
    ]));
    
    $route->get('/users/{id}', fn($req) => Response::json([
        'id' => $req->getAttribute('id'),
        'name' => 'Sample User'
    ]));
    
    $route->post('/echo', fn($req) => Response::json([
        'echo' => $req->getParsedBody()
    ]));
});
