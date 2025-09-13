<?php

namespace App\Core\Db;

interface DbProviderInterface
{
    public function execute(string $sql, ?array $bind = null): int;
    public function getOne(string $sql, ?array $bind = null): mixed;
    public function getRow(string $sql, ?array $bind = null): ?array;
    public function getAll(string $sql, ?array $bind = null): array;
    public function getAssoc(string $sql, ?array $bind = null): array;

    public function begin(): void;
    public function commit(): void;
    public function rollBack(): void;
    public function inTransaction(): bool;
    public function lastInsertId(): int;
}