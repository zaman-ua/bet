<?php

namespace App\Core\Db;

use App\Core\Interface\DbProviderInterface;
use App\Exception\ConfigurationException;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlserver;

class CakeProvider implements DbProviderInterface
{
    private ?Connection $connection = null;
    public function __construct(private array $config) {}

    private function connect(): Connection
    {
        if ($this->connection instanceof Connection) {
            return $this->connection;
        }

        $config = $this->config;
        if(empty($config)) {
            throw new ConfigurationException();
        }

        $driverName = match ($config['driver']) {
            'postgres' => Postgres::class,
            'mssql' => Sqlserver::class,
            default => Mysql::class
        };

        $driver = new $driverName([
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
        ]);

        $this->connection = new Connection([
            'driver' => $driver
        ]);

        if (!empty($config['collation'])) {
            $this->connection->execute("SET collation_connection = '" . $config['collation'] . "'");
        }

        return $this->connection;
    }
    public function execute(string $sql, ?array $bind = null): int
    {
        $statement = $this->connect()->execute($sql, $bind ?? []);
        return $statement->rowCount();
    }

    public function getOne(string $sql, ?array $bind = null): mixed
    {
        $row = $this->getRow($sql, $bind ?? []);
        return $row ? reset($row) : null;
    }

    public function getRow(string $sql, ?array $bind = null): ?array
    {
        $stmt = $this->connect()->execute($sql, $bind ?? []);
        $row = $stmt->fetch('assoc');
        return $row === false ? null : $row;
    }

    public function getAll(string $sql, ?array $bind = null): array
    {
        $stmt = $this->connect()->execute($sql, $bind ?? []);
        $rows = $stmt->fetchAll('assoc');
        return $rows ?: [];
    }

    public function getAssoc(string $sql, ?array $bind = null): array
    {
        $rows = $this->getAll($sql, $bind);
        $out = [];

        if(!empty($rows)) {
            foreach ($rows as $row) {
                // первый элемент делаем ключем
                $key = array_shift($row);

                //особый случай для 2х колонок
                if(count($row ?? []) == 1) {
                    $out[$key] = array_shift($row);
                } else {
                    $out[$key] = $row;
                }
            }
        }

        return $out;
    }

    public function begin(): void
    {
        $this->connect()->begin();
    }

    public function commit(): void
    {
        $this->connect()->commit();
    }

    public function rollBack(): void
    {
        $this->connect()->rollback();
    }

    public function inTransaction(): bool
    {
        return $this->connect()->inTransaction();
    }

    public function lastInsertId(): int
    {
        return (int)$this->connect()->getDriver()->lastInsertId();
    }
}