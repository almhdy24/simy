<?php
namespace Simy\App\Providers;

use Simy\Core\Container;
use Simy\Core\Routing\Router;
use Simy\Core\Routing\RouteRegistrar;
use Simy\Core\Providers\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    protected array $provides = [
        Router::class,
        RouteRegistrar::class
    ];

    public function register(): void
    {
        $container = $this->getContainer();

        $container->addShared(Router::class, function() use ($container) {
            return new Router($container);
        });

        $container->addShared(RouteRegistrar::class, function() use ($container) {
            return new RouteRegistrar($container->get(Router::class));
        });
    }
}
