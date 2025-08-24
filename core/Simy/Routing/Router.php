<?php
declare(strict_types=1);

namespace Simy\Core\Routing;

use Simy\Core\Container;
use Simy\Core\Exceptions\HttpException;
use Simy\Core\Psr\Http\Message\ResponseInterface;
use Simy\Core\Psr\Http\Message\ServerRequestInterface;

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

    public function add(
    string $method,
    string $path,
    $handler,
    array $middleware = []
): string {
    $routeId = uniqid("route_", true);

    // Build full path from group prefix + local path
$fullPath = rtrim($this->currentGroupPrefix ?? '', '/') . '/' . ltrim($path, '/');
$normalizedPath = $this->normalizePath($fullPath);

    $this->routes[] = [
        "id" => $routeId,
        "method" => strtoupper($method),
        "path" => $normalizedPath,
        "handler" => $handler,
        "middleware" => array_merge($this->currentGroupMiddleware, $middleware),
        "name" => null,
    ];

    $this->compiledRoutes = null;

    return $routeId;
}

public function group(
    string $prefix,
    callable $callback,
    array $middleware = []
): void {
    $previousPrefix = $this->currentGroupPrefix ?? '';
    $previousMiddleware = $this->currentGroupMiddleware;


    $this->currentGroupPrefix = rtrim($previousPrefix, '/') . '/' . ltrim($prefix, '/');
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
                foreach ($match["params"] as $key => $value) {
                    $request = $request->withAttribute($key, $value);
                }
                return $this->runHandler($match["route"], $request);
            }
        }

        throw new HttpException("Route not found", 404);
    }
    
  public function urlFor(string $name, array $params = []): string
  {
    if (!isset($this->namedRoutes[$name])) {
      throw new \InvalidArgumentException("Route '{$name}' not found");
    }

    $route = $this->namedRoutes[$name];
    $path = $route["path"];
    $missingParams = [];

    $path = preg_replace_callback(
      "/\{(\w+)\??\}/",
      function ($matches) use ($params, &$missingParams) {
        $param = $matches[1];
        if (!array_key_exists($param, $params)) {
          if (str_ends_with($matches[0], "?}")) {
            return "";
          }
          $missingParams[] = $param;
          return $matches[0];
        }
        return (string) $params[$param];
      },
      $path
    );

    if (!empty($missingParams)) {
      throw new \InvalidArgumentException(
        "Missing required parameters: " . implode(", ", $missingParams)
      );
    }

    return $path;
  }

  public function nameRoute(string $routeId, string $name): void
  {
    foreach ($this->routes as &$route) {
      if ($route["id"] === $routeId) {
        $route["name"] = $name;
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
        return [...$route, "pattern" => $this->buildPattern($route["path"])];
      }, $this->routes);
    }
    return $this->compiledRoutes;
  }

  private function matchRoute(
    array $route,
    string $method,
    string $path
  ): array|false {
    if ($route["method"] !== $method) {
      return false;
    }

    if (!preg_match($route["pattern"], $path, $matches)) {
      return false;
    }

    $params = array_filter(
      $matches,
      fn($key) => !is_numeric($key),
      ARRAY_FILTER_USE_KEY
    );

    foreach ($params as $key => $value) {
      if (empty($value) && !str_contains($route["path"], "{{$key}?}")) {
        return false;
      }
    }

    return [
      "route" => $route,
      "params" => $params,
    ];
  }

  private function runHandler(
        array $route,
        ServerRequestInterface $request
    ): ResponseInterface {
        $handler = $this->resolveHandler($route["handler"]);
        $middlewareStack = $this->resolveMiddleware($route["middleware"]);

        $next = fn(ServerRequestInterface $req) => $handler($req);

        foreach (array_reverse($middlewareStack) as $middleware) {
            $next = fn(ServerRequestInterface $req) => $middleware->handle($req, $next);
        }

        $result = $next($request);
        return \Simy\Core\ResponseFactory::make($result);
    }

    private function resolveMiddleware(array $middlewareNames): array
    {
        $resolved = [];
        
        foreach ($middlewareNames as $middlewareName) {
            // Skip empty middleware names
            if (empty($middlewareName)) {
                continue;
            }
            
            // Check if it's a middleware class that exists
            if (class_exists($middlewareName)) {
                if ($this->container->has($middlewareName)) {
                    $resolved[] = $this->container->get($middlewareName);
                } else {
                    $resolved[] = new $middlewareName();
                }
                continue;
            }
            
            // Check if it's a registered middleware alias
            if ($this->container->has('middleware.' . $middlewareName)) {
                $resolved[] = $this->container->get('middleware.' . $middlewareName);
                continue;
            }
            
            // Log warning for unknown middleware but don't break
            var_dump("Warning: Middleware '{$middlewareName}' not found. Skipping.");
        }
        
        return $resolved;
    }

    private function resolveHandler($handler): callable
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler);

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

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;

            if (!class_exists($class)) {
                throw new \RuntimeException("Controller class {$class} not found");
            }

            if (!method_exists($class, $method)) {
                throw new \RuntimeException("Method {$method} not found in {$class}");
            }

            $controller = $this->container->has($class)
                ? $this->container->get($class)
                : new $class();

            return fn($req) => $controller->$method($req);
        }

        throw new \InvalidArgumentException("Invalid route handler");
    }

    private function normalizePath(string $path): string
{
    // Collapse multiple slashes
    $path = preg_replace('#/+#', '/', $path);

    // Ensure starts with /
    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }

    // Remove trailing slash except root
    if ($path !== '/' && str_ends_with($path, '/')) {
        $path = rtrim($path, '/');
    }

    return $path;
}

    private function buildPattern(string $path): string
    {
        // Convert route parameters to regex patterns
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = preg_replace('/\{(\w+)\?\}/', '(?P<$1>[^/]*)', $pattern);
        
        // Handle the catch-all pattern specifically
        if (str_contains($pattern, '{any:.*}')) {
            $pattern = str_replace('{any:.*}', '(?P<any>.*)', $pattern);
        } elseif (str_contains($pattern, '{any}')) {
            $pattern = str_replace('{any}', '(?P<any>[^/]+)', $pattern);
        }
        
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