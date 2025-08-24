<?php
use Simy\Core\Application;
use Simy\Core\Response;
use Simy\Core\Routing\RouteRegistrar;
use Simy\Core\Psr\Http\Message\ServerRequestInterface;

/** @var RouteRegistrar $route */
$route = Application::getInstance()->getContainer()->get(RouteRegistrar::class);

// ==================== BASIC ROUTES ====================
$route->get('/', fn() => new Response('Welcome to Simy - Home'))->name('home');
$route->get('/welcome', fn() => 'Hello World!');
$route->get('/json', fn() => ['message' => 'Hello JSON']);

// ==================== PARAMETERS ====================
$route->get('/user/{id}', fn($req) => new Response("User ID: " . $req->getAttribute('id')));
$route->get('/post/{category}/{slug}', fn($req) => new Response("{$req->getAttribute('category')} - {$req->getAttribute('slug')}"));
$route->get('/blog/{year}/{month?}/{day?}', fn($req) =>
    new Response("Blog archive: {$req->getAttribute('year')}-{$req->getAttribute('month','01')}-{$req->getAttribute('day','01')}"));

// ==================== HTTP METHODS ====================
$route->post('/submit', fn($req) => Response::json(['received' => $req->getParsedBody()]));
$route->put('/update/{id}', fn($req) => Response::json(['id' => $req->getAttribute('id'), 'data' => $req->getParsedBody()]));
$route->delete('/remove/{id}', fn($req) => Response::json(['deleted' => $req->getAttribute('id')]));

// ==================== GROUPS & NAMED ROUTES ====================
$route->group('/admin', function($route) {
    $route->get('/dashboard', fn() => new Response('Admin Dashboard'))->name('admin.dashboard');
    $route->get('/users', fn() => new Response('Admin Users'))->name('admin.users');
});
$route->get('/about', fn() => new Response('About Us'))->name('about');

// ==================== REDIRECT & DOWNLOAD ====================
$route->get('/old', fn() => (new Response('', 302))->withHeader('Location', '/new'));
$route->get('/new', fn() => new Response('This is the new URL!'));
$route->get('/download', fn() =>
    (new Response("Sample file"))->withHeader('Content-Type', 'text/plain')->withHeader('Content-Disposition', 'attachment; filename=\"sample.txt\"'));

// ==================== FORM + SESSION + QUERY ====================
$route->get('/form', fn() => new Response('<form method="POST" action="/submit"><input name="name"><button>Go</button></form>'));
$route->get('/session', fn() => (session_start() ? new Response('Visits: ' . ($_SESSION['count'] = ($_SESSION['count'] ?? 0) + 1)) : new Response('Session error')));
$route->get('/search', fn($req) => new Response("Search: " . ($req->getQueryParams()['q'] ?? 'none')));

// ==================== VALIDATION ====================
$route->get('/num/{id:\d+}', fn($req) => new Response("Numeric: " . $req->getAttribute('id')));
$route->get('/alpha/{name:[a-zA-Z]+}', fn($req) => new Response("Alpha: " . $req->getAttribute('name')));

// ==================== CONTROLLERS ====================
$route->get('/home', [\Simy\App\Controllers\HomeController::class, 'index']);
$route->get('/user/{id}/profile', [\Simy\App\Controllers\UserController::class, 'profile']);

// ==================== FALLBACK ====================
$route->get('/{any}', fn($req) => new Response("Page '{$req->getAttribute('any')}' not found", 404))->name('fallback');