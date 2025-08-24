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

        if (isset($this->shared[$id])) {
            $instance = $this->resolve($this->shared[$id]);
            $this->instances[$id] = $instance;
            return $instance;
        }

        if (isset($this->bindings[$id])) {
            return $this->resolve($this->bindings[$id]);
        }

        if (class_exists($id)) {
            return $this->resolve($id);
        }

        throw new \RuntimeException("Service not found: {$id}");
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->shared[$id]) || class_exists($id);
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
                        throw new \RuntimeException("Cannot resolve parameter: {$parameter->getName()}");
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