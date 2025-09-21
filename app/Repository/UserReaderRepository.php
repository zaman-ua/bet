<?php

namespace App\Repository;

use App\Core\Interface\DbInterface;
use App\Interface\UserReaderRepositoryInterface;

final class UserReaderRepository implements UserReaderRepositoryInterface
{
    public function __construct(
        private readonly DbInterface $db,
    ) {
    }

    public function getUserById(int $userId, ?bool $status = null) : ?array
    {
        $sql = 'SELECT id, login, name, gender, birth_date, status, is_admin FROM `users` WHERE `id` = :userId ';
        $params['userId'] = $userId;

        if(!is_null($status)) {
            $sql .= ' AND `status` = :status';
            $params['status'] = $status ? 'active' : 'inactive';
        }

        return $this->db->getRow($sql, $params);
    }

    public function getUserIdPwdByLogin(string $login) : ?array
    {
        return $this->db->getRow("SELECT id, password_hash 
            FROM `users` 
            WHERE login = :login AND status = 'active' ", [
                'login' => $login
        ]);
    }

    public function getUserIdByLogin(string $login) : ?int
    {
        return $this->db->getOne('SELECT id FROM `users` WHERE `login` = ?', [$login]);
    }

    public function fetchAll() : ?array
    {
        $all = $this->db->getAll("SELECT
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
            {$this->userAmountSqlFragment()}
            ORDER BY u.id DESC;
        ");

        return $all;
    }

    // странно, а чего ты не в UserAmountRepository ?
    public function fetchAmountsById(int $userId) : ?string
    {
        $amountString = $this->db->getOne("SELECT am.amounts
            FROM users u
            {$this->userAmountSqlFragment()}
            WHERE u.id = :userId", [$userId]);

        return $amountString;
    }

    private function userAmountSqlFragment() : string
    {
        return "
            LEFT JOIN (
                SELECT ua.user_id,
                GROUP_CONCAT(DISTINCT CONCAT(ua.amount, ' ', c.symbol) ORDER BY c.symbol SEPARATOR ';') AS amounts
                FROM user_amounts ua
                JOIN currencies c ON c.id = ua.currency_id
                GROUP BY ua.user_id
            ) am ON am.user_id = u.id
        ";
    }
}