<?php

namespace App\Repository;

use App\Core\Interface\DbInterface;
use App\DTO\UserCreateDTO;
use App\Interface\UserWriterRepositoryInterface;

final class UserWriterRepository implements UserWriterRepositoryInterface
{
    public function __construct(
        private readonly DbInterface $db,
    ) {
    }

    public function createUser(UserCreateDTO $dto) : ?int
    {
        $this->db->execute("INSERT INTO `users` (`login`, `password_hash`, `name`, `gender`, `birth_date`) VALUES (?, ?, ?, ?, ?)", [
            $dto->login,
            $dto->password_hash,
            $dto->name,
            $dto->gender,
            $dto->birth_date
        ]);

        // ид вставленной строки
        return $this->db->lastInsertId();
    }

    public function createUserContact(int $userId, string $type, string $value) : ?int
    {
        $this->db->execute("INSERT INTO `user_contacts` (`user_id`, `type`, `value`) VALUES (?, ?, ?)", [
            $userId,
            $type,
            $value,
        ]);

        return $this->db->lastInsertId();
    }
}