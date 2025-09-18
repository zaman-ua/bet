<?php

namespace App\Core\Db;

use App\Exception\ConfigurationException;
use Throwable;

// для реализации подключения к базе применим паттерн статического сервиса/фасада подобие синглтона, для удобства использования,
// а для того что бы показать что умеем подменять зависимости - делаем его универсальным под несколько провайдеров
// - голый pdo
// - pdo от cakephp (который уже у нас есть после установки phinx)
final class Db
{
    private static ?array  $config = null;
    private static ?DbProviderInterface $provider = null;

    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$provider = null;
    }

    public static function execute(string $sql, ?array $bind = null) : int
    {
        try {
            return self::provider()->execute($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return 0;
            }
        }
    }
    public static function getOne(string $sql, ?array $bind = null): mixed
    {
        try {
            return self::provider()->getOne($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return null;
            }
        }
    }
    public static function getRow(string $sql, ?array $bind = null): ?array
    {
        try {
            return self::provider()->getRow($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return null;
            }
        }
    }
    public static function getAll(string $sql, ?array $bind = null): ?array
    {
        try {
            return self::provider()->getAll($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return null;
            }
        }
    }
    public static function getAssoc(string $sql, ?array $bind = null): ?array
    {
        try {
            return self::provider()->getAssoc($sql, $bind);
        } catch (Throwable $e) {
            if(env('APP_DEBUG') === true) {
                throw $e;
            } else {
                return null;
            }
        }
    }

    public static function begin(): void
    {
        self::provider()->begin();
    }
    public static function commit(): void
    {
        self::provider()->commit();
    }
    public static function rollBack(): void
    {
        self::provider()->rollBack();
    }
    public static function inTransaction(): bool
    {
        return self::provider()->inTransaction();
    }
    public static function lastInsertId(): int
    {
        return self::provider()->lastInsertId();
    }

    private static function provider(): DbProviderInterface
    {
        if (self::$provider instanceof DbProviderInterface) {
            return self::$provider;
        }

        $config = self::$config ?? throw new ConfigurationException('Db is not configured');

        // выбор драйвера по конфигу
        $provider = $config['provider'];
        $provider = __NAMESPACE__ . '\\' . $provider;

        if (!class_exists($provider)) {
            throw new \RuntimeException("Provider class not found: {$provider}");
        }

        return self::$provider = new $provider($config);
    }
}