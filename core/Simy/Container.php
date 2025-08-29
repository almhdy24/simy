<?php
declare(strict_types=1);

namespace Simy\Core;

class Container
{
  private array $bindings = [];
  private array $shared = [];
  private array $instances = [];

  public function add(string $id, $concrete): void
  {
    $this->bindings[$id] = $concrete;
  }

  public function addShared(string $id, $concrete): void
  {
    $this->shared[$id] = $concrete;
  }

  public function get(string $id)
  {
    if (isset($this->instances[$id])) {
      return $this->instances[$id];
    }

    // Check shared registrations
    if (isset($this->shared[$id])) {
      $instance = $this->resolve($this->shared[$id]);
      $this->instances[$id] = $instance;
      return $instance;
    }

    // Check bindings
    if (isset($this->bindings[$id])) {
      $resolved = $this->resolve($this->bindings[$id]);

      // If it's a shared binding, store the instance
      if (array_key_exists($id, $this->shared)) {
        $this->instances[$id] = $resolved;
      }

      return $resolved;
    }

    // Auto-wiring for classes and interfaces
    if (interface_exists($id) || class_exists($id)) {
      // Check if we have a binding for this interface/class
      foreach ($this->bindings as $bindingId => $concrete) {
        if (
          is_string($bindingId) &&
          (is_a($concrete, $id, true) || is_subclass_of($concrete, $id))
        ) {
          return $this->resolve($concrete);
        }
      }

      // Fall back to class instantiation
      if (class_exists($id)) {
        return $this->resolve($id);
      }
    }

    throw new \RuntimeException("Service not found: {$id}");
  }

  public function has(string $id): bool
  {
    return isset($this->bindings[$id]) ||
      isset($this->shared[$id]) ||
      class_exists($id);
  }

  private function resolve($concrete)
  {
    if (is_callable($concrete)) {
      return $concrete($this);
    }

    if (is_string($concrete) && class_exists($concrete)) {
      $reflection = new \ReflectionClass($concrete);

      if (!$reflection->isInstantiable()) {
        throw new \RuntimeException("Class {$concrete} is not instantiable");
      }

      $constructor = $reflection->getConstructor();

      if ($constructor === null) {
        return new $concrete();
      }

      $parameters = $constructor->getParameters();
      $dependencies = [];

      foreach ($parameters as $parameter) {
        $type = $parameter->getType();

        if ($type === null || $type->isBuiltin()) {
          if ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
          } else {
            throw new \RuntimeException(
              "Cannot resolve parameter: {$parameter->getName()}"
            );
          }
        } else {
          $dependencies[] = $this->get($type->getName());
        }
      }

      return $reflection->newInstanceArgs($dependencies);
    }

    return $concrete;
  }
}
