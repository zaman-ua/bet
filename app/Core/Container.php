<?php

namespace App\Core;

use Closure;
use RuntimeException;

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

        if (!isset($this->definitions[$id])) {
            throw new RuntimeException("Service '{$id}' is not registered in the container");
        }

        $definition = $this->definitions[$id];
        $value = ($definition['factory'])($this);

        if ($definition['shared']) {
            $this->instances[$id] = $value;
        }

        return $value;
    }
}