<?php

namespace App\DTO;

final class UserCreateDTO
{
    public function __construct(
        public string $login,
        public string $password_hash,
        public string $name,
        public string $gender,
        public string $birth_date
    ) {}
}