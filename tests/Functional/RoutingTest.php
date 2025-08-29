<?php
declare(strict_types = 1);

namespace Simy\Tests\Functional;

use Core\App;
use Core\Request;
use PHPUnit\Framework\TestCase;

class RoutingTest extends TestCase
{
  public function testHomeRoute() {
    // Initialize your application
    $app = App::create(dirname(__DIR__, 2));

    // Create a test request
    $request = Request::createFromGlobals()
    ->withMethod('GET')
    ->withUri($this->createUri('/'));

    // Capture output
    ob_start();
    $app->run($request);
    $output = ob_get_clean();

    $this->assertStringContainsString('Welcome to the homepage', $output);
  }

  private function createUri(string $path): \Psr\Http\Message\UriInterface
{
    return (new \Core\Uri('http', 'localhost', $path));
}
}