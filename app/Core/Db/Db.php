<?php

namespace App\Core\Db;

use App\Core\Interface\DbInterface;
use App\Core\Interface\DbProviderInterface;
use App\Exception\ConfigurationException;
use RuntimeException;
use Throwable;


final class Db implements DbInterface
{
    private ?DbProviderInterface $provider = null;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function execute(string $sql, ?array $bind = null) : int
    {
        try {
            return $this->provider()->execute($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return 0;
            }
        }
    }
    public function getOne(string $sql, ?array $bind = null): mixed
    {
        try {
            return $this->provider()->getOne($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return null;
            }
        }
    }
    public function getRow(string $sql, ?array $bind = null): ?array
    {
        try {
            return $this->provider()->getRow($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return null;
            }
        }
    }
    public function getAll(string $sql, ?array $bind = null): ?array
    {
        try {
            return $this->provider()->getAll($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return null;
            }
        }
    }
    public function getAssoc(string $sql, ?array $bind = null): ?array
    {
        try {
            return $this->provider()->getAssoc($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return null;
            }
        }
    }

    public function begin(): void
    {
        $this->provider()->begin();
    }
    public function commit(): void
    {
        $this->provider()->commit();
    }
    public function rollBack(): void
    {
        $this->provider()->rollBack();
    }
    public function inTransaction(): bool
    {
        return $this->provider()->inTransaction();
    }
    public function lastInsertId(): int
    {
        return $this->provider()->lastInsertId();
    }

    private function provider(): DbProviderInterface
    {
        if ($this->provider instanceof DbProviderInterface) {
            return $this->provider;
        }

        if ($this->config === []) {
            throw new ConfigurationException('Database configuration is empty');
        }

        $providerClass = $this->config['provider'] ?? null;

        if (!is_string($providerClass) || $providerClass === '') {
            throw new ConfigurationException('Database provider is not configured');
        }

        $providerClass = $this->normalizeProviderClass($providerClass);

        if (!class_exists($providerClass)) {
            throw new RuntimeException("Provider class not found: {$providerClass}");
        }

        $provider = new $providerClass($this->config);

        if (!$provider instanceof DbProviderInterface) {
            throw new RuntimeException("Provider {$providerClass} must implement " . DbProviderInterface::class);
        }

        return $this->provider = $provider;
    }

    private function normalizeProviderClass(string $providerClass): string
    {
        if (class_exists($providerClass)) {
            return $providerClass;
        }

        $providerClass = ltrim($providerClass, '\\');
        $prefixed = __NAMESPACE__ . '\\' . $providerClass;

        return $prefixed;
    }
}