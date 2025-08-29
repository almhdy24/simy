<?php
declare(strict_types=1);

namespace Simy\Tests;

use Core\Container;
use Core\Routing\RouteRegistrar;
use Core\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouteRegistrarTest extends TestCase
{
    public function testRouteRegistration()
    {
        $container = new Container();
        $router = new Router($container);
        $registrar = new RouteRegistrar($router);
        
        $registrar->get('/test', 'TestHandler');
        
        $this->assertCount(1, $router->getRoutes());
        $this->assertEquals('GET', $router->getRoutes()[0]['method']);
        $this->assertEquals('/test', $router->getRoutes()[0]['path']);
    }

    public function testNamedRoutes()
    {
        $container = new Container();
        $router = new Router($container);
        $registrar = new RouteRegistrar($router);
        
        $registrar->get('/users', 'UserController@index')->name('users.index');
        
        $this->assertArrayHasKey('users.index', $router->getNamedRoutes());
    }
}