<?php

namespace App\Repository;

use App\Core\Db\Db;
use App\Domain\Money;
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
            FROM `users` 
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

    public function fetchAll() : ?array
    {
        $all = Db::getAll("SELECT
                u.id, u.created_at, u.login, u.name, u.gender, u.birth_date, u.status, u.is_admin,
                p.phones, e.emails, a.addresses, am.amounts
            FROM users u
            LEFT JOIN (
                SELECT user_id, GROUP_CONCAT(value ORDER BY id SEPARATOR '; ') AS phones
                FROM user_contacts
                WHERE type = 'phone'
                GROUP BY user_id
            ) p ON p.user_id = u.id
            LEFT JOIN (
                SELECT user_id, GROUP_CONCAT(value ORDER BY id SEPARATOR '; ') AS emails
                FROM user_contacts
                WHERE type = 'email'
                GROUP BY user_id
            ) e ON e.user_id = u.id
            LEFT JOIN (
                SELECT user_id, GROUP_CONCAT(value ORDER BY id SEPARATOR '; ') AS addresses
                FROM user_contacts
                WHERE type = 'address'
                GROUP BY user_id
            ) a ON a.user_id = u.id
            LEFT JOIN (
                SELECT ua.user_id,
                GROUP_CONCAT(DISTINCT CONCAT(ua.amount, ' ', c.symbol) ORDER BY c.symbol SEPARATOR ';') AS amounts
                FROM user_amounts ua
                JOIN currencies c ON c.id = ua.currency_id
                GROUP BY ua.user_id
            ) am ON am.user_id = u.id;
        ");

        if(!empty($all)) {
            foreach ($all as $key => $user) {
                if(!empty($user['amounts'])) {
                    $amountsArray = explode(';', $user['amounts']);
                    $all[$key]['amounts_array'] = $this->processAmounts($amountsArray);
                }
            }
        }

        return $all;
    }

    public function fetchAmountsById(int $userId) : array
    {
        $amountString = Db::getOne("SELECT am.amounts
            FROM users u
            LEFT JOIN (
                SELECT ua.user_id,
                GROUP_CONCAT(DISTINCT CONCAT(ua.amount, ' ', c.symbol) ORDER BY c.symbol SEPARATOR ';') AS amounts
                FROM user_amounts ua
                JOIN currencies c ON c.id = ua.currency_id
                GROUP BY ua.user_id
            ) am ON am.user_id = u.id
            WHERE u.id = :userId", [$userId]);

        if(!empty($amountString)) {
            $amountsArray = explode(';', $amountString);
            return $this->processAmounts($amountsArray);
        }

        return [];
    }

    private function processAmounts(array $amounts) : array
    {
        $result = [];

        if(!empty($amounts)) {
            foreach ($amounts as $amount) {
                [$amountRaw, $currencyCode] = explode(' ', $amount);
                $money = Money::fromRaw($amountRaw, null, $currencyCode);

                $result[] = $money;
            }
        }

        return $result;
    }
}