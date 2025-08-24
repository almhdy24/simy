<?php
use Simy\Core\Application;
use Simy\Core\Response;
use Simy\Core\Routing\RouteRegistrar;

/** @var RouteRegistrar $route */
$route = Application::getInstance()->getContainer()->get(RouteRegistrar::class);

// ==================== API V1 ====================
$route->group('/api/v1', function($route) {
    
    // --- Auth ---
    $route->post('/auth/login', fn($req) => Response::json(['token' => 'jwt_here']));
    $route->post('/auth/register', fn($req) => Response::json(['msg' => 'Registered', 'user' => $req->getParsedBody()], 201));
    $route->get('/auth/me', fn() => Response::json(['id' => 1, 'name' => 'John']));

    // --- Users (CRUD) ---
    $route->get('/users', fn() => Response::json(['users' => [['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']]]));
    $route->get('/users/{id}', fn($req) => Response::json(['id' => $req->getAttribute('id'), 'name' => 'Sample User']));
    $route->post('/users', fn($req) => Response::json(['msg' => 'User created', 'data' => $req->getParsedBody()], 201));
    $route->put('/users/{id}', fn($req) => Response::json(['msg' => 'User updated', 'id' => $req->getAttribute('id')]));
    $route->delete('/users/{id}', fn($req) => Response::json(['msg' => 'User deleted', 'id' => $req->getAttribute('id')]));

    // --- Nested Example ---
    $route->get('/users/{id}/posts', fn($req) => Response::json(['user_id' => $req->getAttribute('id'), 'posts' => [['id' => 1, 'title' => 'Hello']]]));

    // --- Posts (CRUD + nested comments) ---
    $route->get('/posts', fn() => Response::json(['posts' => [['id' => 1, 'title' => 'First Post']]]));
    $route->get('/posts/{id}', fn($req) => Response::json(['id' => $req->getAttribute('id'), 'title' => 'Sample Post']));
    $route->post('/posts', fn($req) => Response::json(['msg' => 'Post created', 'data' => $req->getParsedBody()], 201));
    $route->get('/posts/{id}/comments', fn($req) => Response::json(['post_id' => $req->getAttribute('id'), 'comments' => [['id' => 1, 'text' => 'Nice!']]]));

    // --- Utilities ---
    $route->get('/health', fn() => Response::json(['status' => 'ok', 'service' => 'Simy']));
    $route->get('/status', fn() => Response::json(['version' => '1.0.0']));
    $route->post('/echo', fn($req) => Response::json(['echo' => $req->getParsedBody()]));
});

// ==================== API V2 (show versioning) ====================
$route->group('/api/v2', function($route) {
    $route->get('/users', fn() => Response::json(['version' => 'v2', 'users' => [['id' => 1, 'name' => 'John v2']]]));
    $route->get('/status', fn() => Response::json(['version' => '2.0.0', 'msg' => 'API v2 active']));
});