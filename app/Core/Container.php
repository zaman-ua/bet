<?php

namespace App\Core;

use Closure;
use RuntimeException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

final class Container
{
    /** @var array<string, array{factory: Closure, shared: bool}> */
    private array $definitions = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    public function set(string $id, Closure $factory, bool $shared = true): void
    {
        $this->definitions[$id] = [
            'factory' => $factory,
            'shared' => $shared,
        ];
    }

    public function factory(string $id, Closure $factory): void
    {
        $this->set($id, $factory, false);
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (isset($this->definitions[$id])) {
            $definition = $this->definitions[$id];
            $value = ($definition['factory'])($this);

            if ($definition['shared']) {
                $this->instances[$id] = $value;
            }

            return $value;
        }

        if (class_exists($id)) {
            $instance = $this->build($id);
            $this->instances[$id] = $instance;

            return $instance;
        }

        throw new RuntimeException("Service '{$id}' is not registered in the container");
    }

    public function build(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class '{$class}' is not instantiable");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = $this->resolveParameter($parameter, $class);
        }

        return $reflection->newInstanceArgs($arguments);
    }

    private function resolveParameter(ReflectionParameter $parameter, string $class): mixed
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this->get($type->getName());
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new RuntimeException("Unable to resolve dependency '{$parameter->getName()}' when building '{$class}'");
    }
}