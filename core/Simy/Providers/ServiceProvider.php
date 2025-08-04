<?php
namespace Simy\Core\Providers;

use Simy\Core\Container;

interface ServiceProvider
{
/**
* Register bindings in the container
*/
public function register(Container $container): void;

/**
* Boot services after registration
* (Optional - for complex providers)
*/
public function boot(): void;
}