<?php

namespace App\Interface;

use App\DTO\UserCreateDTO;

interface UserWriterRepositoryInterface
{
    public function createUser(UserCreateDTO $dto): ?int;

    public function createUserContact(int $userId, string $type, string $value): ?int;

}