<?php

namespace App\Repository;

use App\Core\Db\Db;

final class UserAmountRepository
{
    public function lockGet(int $userId, int $currencyId): ?array
    {
        return Db::getRow('SELECT user_id, currency_id, amount
             FROM user_amounts
             WHERE user_id = :userId AND currency_id = :currencyId
             FOR UPDATE', [
                'userId' => $userId, 'currencyId'=>$currencyId
        ]);
    }

    public function debit(int $userId, int $currencyId, int $amount): void
    {
        Db::execute('UPDATE user_amounts
             SET amount = amount - :amount
             WHERE user_id = :userId AND currency_id = :currencyId', [
                'amount' => $amount,
                'userId' => $userId,
                'currencyId' => $currencyId
        ]);
    }

    public function credit(int $userId, int $currencyId, int $amount): void
    {
        Db::execute('UPDATE user_amounts
             SET amount = amount + :amount
             WHERE user_id = :userId AND currency_id = :currencyId', [
                'amount' => $amount,
                'userId' => $userId,
                'currencyId' => $currencyId
            ]);
    }
}