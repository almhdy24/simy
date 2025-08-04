<?php
declare(strict_types=1);

namespace Simy\Core\Routing;

class RouteRegistrar
{
    private Router $router;
    private ?string $currentNamePrefix = null;
    private array $currentGroupMiddleware = [];
    private ?string $currentRouteName = null;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function get(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $handler, $middleware);
    }

    public function put(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $uri, $handler, $middleware);
    }

    public function patch(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $uri, $handler, $middleware);
    }

    public function delete(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $uri, $handler, $middleware);
    }

    public function name(string $name): self
    {
        if ($this->currentRouteName === null) {
            throw new \RuntimeException('No route to name');
        }

        $fullName = $this->currentNamePrefix 
            ? $this->currentNamePrefix . $name
            : $name;

        $this->router->nameRoute($this->currentRouteName, $fullName);
        $this->currentRouteName = null;
        
        return $this;
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousPrefix = $this->currentNamePrefix;
        $previousMiddleware = $this->currentGroupMiddleware;

        $this->currentNamePrefix = $previousPrefix ? $previousPrefix . $prefix : $prefix;
        $this->currentGroupMiddleware = array_merge($previousMiddleware, $middleware);

        try {
            $callback($this);
        } finally {
            $this->currentNamePrefix = $previousPrefix;
            $this->currentGroupMiddleware = $previousMiddleware;
        }
    }

    private function addRoute(string $method, string $uri, $handler, array $middleware): self
    {
        $middleware = array_merge($this->currentGroupMiddleware, $middleware);
        $this->currentRouteName = $this->router->add(
            $method,
            $uri,
            $handler,
            $middleware
        );
        
        return $this;
    }
}