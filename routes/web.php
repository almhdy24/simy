<?php
use Simy\Core\Application;
use Simy\Core\Response;
use Simy\Core\Routing\RouteRegistrar;
use Psr\Http\Message\ServerRequestInterface;

/** @var RouteRegistrar $route */
$route = Application::getInstance()->getContainer()->get(RouteRegistrar::class);

// Basic route with closure
$route->get('/', function() {
    return new Response('Welcome to Simy Framework');
});

// Named route
$route->get('/about', function() {
    return new Response('About Simy Framework');
})->name('about');

// Controller route
$route->get('/home', [\Simy\App\Controllers\HomeController::class, 'index']);

// Route with parameters
$route->get('/users/{id}', function(ServerRequestInterface $request) {
    $id = $request->getAttribute('id');
    return new Response("User ID: {$id}");
});

// Route with optional parameter
$route->get('/posts/{slug}/{page?}', function(ServerRequestInterface $request) {
    $slug = $request->getAttribute('slug');
    $page = $request->getAttribute('page', 1);
    return new Response("Showing post: {$slug}, page {$page}");
});

// Route group with middleware
$route->group('/admin', function($route) {
    $route->get('/dashboard', [\Simy\App\Controllers\AdminController::class, 'dashboard']);
    $route->get('/settings', [\Simy\App\Controllers\AdminController::class, 'settings']);
}, ['auth']);

// Form submission example
$route->post('/contact', [\Simy\App\Controllers\ContactController::class, 'submit']);