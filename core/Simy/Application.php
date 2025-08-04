<?php
declare(strict_types = 1);

namespace Simy\Core;

use Simy\Core\Container;
use Simy\Core\Providers\ServiceProvider;
use Simy\Core\Routing\Router;
use Simy\Core\Routing\RouteRegistrar;
use League\Container\ReflectionContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class Application
{
  private static ?self $instance = null;
  private Container $container;
  private string $basePath;
  private bool $bootstrapped = false;

  private function __construct(string $basePath) {
    $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
  }

  public static function create(string $basePath): self
  {
    if (self::$instance !== null) {
      throw new RuntimeException('Application already initialized');
    }

    self::$instance = new self($basePath);
    self::$instance->bootstrap();
    return self::$instance;
  }

  public static function getInstance(): self
  {
    if (self::$instance === null) {
      throw new RuntimeException(
        'Application not initialized. Call App::create() first.'
      );
    }
    return self::$instance;
  }

  private function bootstrap(): void
  {
    if ($this->bootstrapped) {
      return;
    }

    // Initialize container
    $this->container = new Container();

    // Register container with itself (using new v4 syntax)
    $this->container->addShared('container', $this->container);

    $this->loadConfiguration();
    $this->registerCoreBindings();
    $this->registerProviders();
    $this->bootstrapped = true;
  }

  public function getContainer(): Container
  {
    return $this->container;
  }

  public function getBasePath(): string
  {
    return $this->basePath;
  }

  public function run(): void
  {
    $this->loadRoutes();

    try {
      $router = $this->container->get(Router::class);
      $request = $this->container->get(ServerRequestInterface::class);

      $response = $router->dispatch($request);
      $response->send();
    } catch (\Throwable $e) {
      $this->handleException($e);
    }
  }

  private function loadConfiguration(): void
  {
    $configPath = $this->basePath . '/app/config/app.php';
    if (file_exists($configPath)) {
      Config::loadFromArray(require $configPath);
    }
  }


  private function registerCoreBindings(): void
  {
    // Singleton bindings (shared)
    $this->container->addShared(Container::class, $this->container);

    // Router needs the container instance
    $this->container->addShared(Router::class, function() {
      return new Router($this->container);
    });

    // RouteRegistrar needs Router instance
    $this->container->addShared(RouteRegistrar::class, function() {
      return new RouteRegistrar($this->container->get(Router::class));
    });

    // Other bindings...
    $this->container->add(ServerRequestInterface::class, function() {
      return Request::createFromGlobals();
    });

    $this->container->addShared('exceptionHandler', function() {
      return new Exceptions\ErrorHandler(
        Config::get('app.debug', false),
        Config::get('app.error_log')
      );
    });
  }

  private function registerProviders(): void
  {
    $providers = Config::get('app.providers', []);

    foreach ($providers as $provider) {
      if (is_string($provider) && class_exists($provider)) {
        // Pass the container instance to the provider
        $providerInstance = new $provider($this->container);

        if ($providerInstance instanceof \Core\Providers\ServiceProvider) {
          $providerInstance->register();

          if (method_exists($providerInstance, 'boot')) {
            $providerInstance->boot();
          }
        }
      }
    }
  }

  private function loadRoutes(): void
  {
    $routeFiles = [
      $this->basePath . '/routes/web.php',
      $this->basePath . '/routes/api.php'
    ];

    foreach ($routeFiles as $file) {
      if (file_exists($file)) {
        require $file;
      }
    }
  }

  private function handleException(\Throwable $e): void
  {
    $handler = $this->container->get('exceptionHandler');
    $response = $handler->handle($e);
    $response->send();
  }

  public function terminate(): void
  {
    self::$instance = null;
    $this->bootstrapped = false;
  }
}