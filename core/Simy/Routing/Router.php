<?php
declare(strict_types=1);

namespace Simy\Core\Routing;

use Simy\Core\Container;
use Simy\Core\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    private array $routes = [];
    private array $namedRoutes = [];
    private ?array $compiledRoutes = null;
    private Container $container;
    private ?string $currentGroupPrefix = null;
    private array $currentGroupMiddleware = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function add(string $method, string $path, $handler, array $middleware = []): string
    {
        $routeId = uniqid('route_', true);
        $normalizedPath = $this->currentGroupPrefix 
            ? $this->normalizePath($this->currentGroupPrefix . $path)
            : $this->normalizePath($path);

        $this->routes[] = [
            'id' => $routeId,
            'method' => strtoupper($method),
            'path' => $normalizedPath,
            'handler' => $handler,
            'middleware' => array_merge($this->currentGroupMiddleware, $middleware),
            'name' => null
        ];

        // Invalidate compiled routes cache
        $this->compiledRoutes = null;

        return $routeId;
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousPrefix = $this->currentGroupPrefix ?? '';
        $this->currentGroupPrefix = $previousPrefix . $this->normalizePath($prefix);

        $previousMiddleware = $this->currentGroupMiddleware;
        $this->currentGroupMiddleware = array_merge($previousMiddleware, $middleware);

        try {
            $callback($this);
        } finally {
            $this->currentGroupPrefix = $previousPrefix;
            $this->currentGroupMiddleware = $previousMiddleware;
            $this->compiledRoutes = null;
        }
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $path = $this->normalizePath($request->getUri()->getPath());
        $method = $request->getMethod();

        foreach ($this->getCompiledRoutes() as $route) {
            $match = $this->matchRoute($route, $method, $path);
            if ($match !== false) {
                foreach ($match['params'] as $key => $value) {
                    $request = $request->withAttribute($key, $value);
                }
                return $this->runHandler($match['route'], $request);
            }
        }

        throw new HttpException('Route not found', 404);
    }

    public function urlFor(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route '{$name}' not found");
        }

        $route = $this->namedRoutes[$name];
        $path = $route['path'];
        $missingParams = [];

        $path = preg_replace_callback('/\{(\w+)\??\}/', function($matches) use ($params, &$missingParams) {
            $param = $matches[1];
            if (!array_key_exists($param, $params)) {
                if (str_ends_with($matches[0], '?}')) {
                    return '';
                }
                $missingParams[] = $param;
                return $matches[0];
            }
            return (string)$params[$param];
        }, $path);

        if (!empty($missingParams)) {
            throw new \InvalidArgumentException(
                "Missing required parameters: " . implode(', ', $missingParams)
            );
        }

        return $path;
    }

    public function nameRoute(string $routeId, string $name): void
    {
        foreach ($this->routes as &$route) {
            if ($route['id'] === $routeId) {
                $route['name'] = $name;
                $this->namedRoutes[$name] = $route;
                $this->compiledRoutes = null;
                return;
            }
        }

        throw new \RuntimeException("Route with ID {$routeId} not found");
    }

    private function getCompiledRoutes(): array
    {
        if ($this->compiledRoutes === null) {
            $this->compiledRoutes = array_map(function ($route) {
                return [
                    ...$route,
                    'pattern' => $this->buildPattern($route['path'])
                ];
            }, $this->routes);
        }
        return $this->compiledRoutes;
    }

    private function matchRoute(array $route, string $method, string $path): array|false
    {
        if ($route['method'] !== $method) {
            return false;
        }

        if (!preg_match($route['pattern'], $path, $matches)) {
            return false;
        }

        $params = array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);

        foreach ($params as $key => $value) {
            if (empty($value) && !str_contains($route['path'], "{{$key}?}")) {
                return false;
            }
        }

        return [
            'route' => $route,
            'params' => $params
        ];
    }

    private function runHandler(array $route, ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->resolveHandler($route['handler']);
        $middlewareStack = $route['middleware'];

        $next = fn(ServerRequestInterface $req) => $handler($req);

        foreach (array_reverse($middlewareStack) as $middleware) {
            if (!is_string($middleware)) {
                throw new \InvalidArgumentException('Middleware must be a class name string');
            }
            $next = fn(ServerRequestInterface $req) => 
                $this->container->get($middleware)->handle($req, $next);
        }

        return $next($request);
    }

    private function resolveHandler($handler): callable
{
    if (is_callable($handler)) {
        return $handler;
    }

    if (is_array($handler) && count($handler) === 2) {
        [$class, $method] = $handler;
        
        // Ensure controller exists
        if (!class_exists($class)) {
            throw new \RuntimeException("Controller class {$class} not found");
        }

        // Ensure method exists
        if (!method_exists($class, $method)) {
            throw new \RuntimeException("Method {$method} not found in {$class}");
        }

        // Get instance from container or create new one
        $controller = $this->container->has($class) 
            ? $this->container->get($class)
            : new $class();

        return fn($req) => $controller->$method($req);
    }

    throw new \InvalidArgumentException('Invalid route handler');
}

    private function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        return $path === '' ? '/' : "/{$path}";
    }

    private function buildPattern(string $path): string
    {
        $pattern = preg_replace('/\{(\w+)\}|:(\w+)/', '(?P<$1>[^/]+)', $path);
        $pattern = preg_replace('/\{(\w+)\?\}/', '(?P<$1>[^/]*)', $pattern);
        return "#^{$pattern}$#";
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }
}