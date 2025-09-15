<?php

namespace App\Repository;

use App\Core\Db\Db;
use App\DTO\UserCreateDTO;

final class UserRepository
{
    public function getUserById(int $userId, ?bool $status = null) : ?array
    {
        $sql = 'SELECT id, login, name, gender, birth_date, status, is_admin FROM `users` WHERE `id` = :userId ';
        $params['userId'] = $userId;

        if(!is_null($status)) {
            $sql .= ' AND `status` = :status';
            $params['status'] = $status ? 'active' : 'inactive';
        }

        return Db::getRow($sql, $params);
    }

    public function getUserIdPwdByLogin(string $login) : ?array
    {
        return Db::getRow("SELECT id, password_hash 
            FROM users 
            WHERE login = :login AND status = 'active' ", [
                'login' => $login
        ]);
    }

    public function getUserIdByLogin(string $login) : ?int
    {
        return Db::getOne('SELECT id FROM `users` WHERE `login` = ?', [$login]);
    }

    public function createUser(UserCreateDTO $dto) : ?int
    {
        Db::execute("INSERT INTO `users` (`login`, `password_hash`, `name`, `gender`, `birth_date`) VALUES (?, ?, ?, ?, ?)", [
            $dto->login,
            $dto->password_hash,
            $dto->name,
            $dto->gender,
            $dto->birth_date
        ]);

        // ид вставленной строки
        return Db::lastInsertId();
    }

    public function createUserContact(int $userId, string $type, string $value) : ?int
    {
        Db::execute("INSERT INTO `user_contacts` (`user_id`, `type`, `value`) VALUES (?, ?, ?)", [
            $userId,
            $type,
            $value,
        ]);

        return Db::lastInsertId();
    }
}