<?php
declare(strict_types = 1);

namespace Simy\Tests;

use Core\App;
use Core\Container;
use Core\Request;
use Core\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
  private Router $router;
  private Container $container;

  protected function setUp(): void
  {
    $this->container = new Container();
    $this->router = new Router($this->container);
  }

  public function testBasicRouteMatching() {
    $this->router->add('GET', '/users', 'handler1');

    $request = Request::createFromGlobals()
    ->withMethod('GET')
    ->withUri($this->createUri('/users'));

    $response = $this->router->dispatch($request);

    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testParameterExtraction() {
    $this->router->add('GET', '/users/{id}', function($request) {
      $this->assertEquals('89', $request->getAttribute('id'));
      return new \Core\Response("User ID: 89");
    });

    $request = Request::createFromGlobals()
    ->withMethod('GET')
    ->withUri($this->createUri('/users/89'));

    $response = $this->router->dispatch($request);

    $this->assertStringContainsString('User ID: 89', (string)$response->getBody());
  }

  public function testMethodMismatch() {
    $this->router->add('POST', '/users', 'handler3');

    $request = Request::createFromGlobals()
    ->withMethod('GET')
    ->withUri($this->createUri('/users'));

    $this->expectException(\Core\Exceptions\HttpException::class);
    $this->expectExceptionCode(404);

    $this->router->dispatch($request);
  }

private function createUri(string $path): \Psr\Http\Message\UriInterface
{
    return (new \Core\Uri('http', 'localhost', $path));
}
}