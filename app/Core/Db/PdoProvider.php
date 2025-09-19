<?php

namespace App\Core\Db;

use App\Core\Interface\DbProviderInterface;
use App\Exception\ConfigurationException;
use PDO;
use PDOStatement;

class PdoProvider implements DbProviderInterface
{
    private ?PDO $pdo = null;
    public function __construct(private array $config) {}

    private function connect(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $config = $this->config;
        if(empty($config)) {
            throw new ConfigurationException();
        }

        $this->pdo = new PDO(
            "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']};timezone={$config['timezone']};",
            $config['username'],
            $config['password'],
            $config['options']
        );

        if (!empty($config['collation'])) {
            $this->pdo->exec("SET collation_connection = '" . $config['collation'] . "'");
        }

        return $this->pdo;
    }

    public function execute(string $sql, ?array $bind = null): int
    {
        $stmt = $this->prepareAndBind($sql, $bind);
        return $stmt->rowCount();
    }

    public function getOne(string $sql, ?array $bind = null): mixed
    {
        $row = $this->getRow($sql, $bind);
        return $row ? reset($row) : null;
    }

    public function getRow(string $sql, ?array $bind = null): ?array
    {
        $stmt = $this->prepareAndBind($sql, $bind);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function getAll(string $sql, ?array $bind = null): array
    {
        $stmt = $this->prepareAndBind($sql, $bind);
        $rows = $stmt->fetchAll();
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
        $this->connect()->beginTransaction();
    }

    public function commit(): void
    {
        $this->connect()->commit();
    }

    public function rollBack(): void
    {
        $this->connect()->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->connect()->inTransaction();
    }

    public function lastInsertId(): int
    {
        return (int) $this->connect()->lastInsertId();
    }

    private function prepareAndBind(string $sql, ?array $bind): PDOStatement
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($bind);
        return $stmt;
    }
}