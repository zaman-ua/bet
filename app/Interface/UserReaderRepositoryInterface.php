<?php

namespace App\Interface;

interface UserReaderRepositoryInterface
{
    public function getUserById(int $userId, ?bool $status = null): ?array;

    public function getUserIdPwdByLogin(string $login): ?array;

    public function getUserIdByLogin(string $login): ?int;

    public function fetchAll(): ?array;

    public function fetchAmountsById(int $userId): array;
}