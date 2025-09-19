<?php

namespace App\Interface;

use App\DTO\UserCreateDTO;

interface UserRepositoryInterface
{
    public function getUserById(int $userId, ?bool $status = null): ?array;

    public function getUserIdPwdByLogin(string $login): ?array;

    public function getUserIdByLogin(string $login): ?int;

    public function createUser(UserCreateDTO $dto): ?int;

    public function createUserContact(int $userId, string $type, string $value): ?int;

    public function fetchAll(): ?array;

    public function fetchAmountsById(int $userId): array;
}