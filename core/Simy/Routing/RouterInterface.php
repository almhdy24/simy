<?php
declare(strict_types=1);

namespace Simy\Core\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    public function add(
        string $method, 
        string $path, 
        $handler, 
        array $middleware = []
    ): string;

    public function nameRoute(string $routeId, string $name): void;
    public function group(string $prefix, callable $callback, array $middleware = []): void;
    public function dispatch(ServerRequestInterface $request): ResponseInterface;
    public function urlFor(string $name, array $params = []): string;
    
    // For testing
    public function getRoutes(): array;
    public function getNamedRoutes(): array;
}