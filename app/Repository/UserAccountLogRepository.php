<?php

namespace App\Repository;

use App\Core\Db\Db;
use App\DTO\UserAmountLogCreateDTO;

final class UserAccountLogRepository
{
    //      'admin_adjust','bet_place','bet_win',
    //
    //  эти статусы пока остануться без реализации: 'deposit','withdraw','refund'

    public function logBetPlace(UserAmountLogCreateDTO $dto): void
    {
        Db::execute('INSERT INTO user_amount_logs (user_id, currency_id, bet_id, type, amount, comment)
             VALUES (?, ?, ?, ?, ?, ?)', [
                 $dto->userId,
                 $dto->currencyId,
                 $dto->betId ?? 0,
                 'bet_place',
                 - $dto->amount,
                 $dto->comment ?? 'bet_place'
        ]);
    }

    public function logBetWin(UserAmountLogCreateDTO $dto): void
    {
        Db::execute('INSERT INTO user_amount_logs (user_id, currency_id, bet_id, type, amount, comment)
             VALUES (?, ?, ?, ?, ?, ?)', [
            $dto->userId,
            $dto->currencyId,
            $dto->betId ?? 0,
            'bet_win',
            $dto->amount,
            $dto->comment ?? 'bet_win'
        ]);
    }

    public function logAdminAdjust(UserAmountLogCreateDTO $dto): void
    {
        Db::execute('INSERT INTO user_amount_logs (user_id, currency_id, bet_id, type, amount, comment)
             VALUES (?, ?, ?, ?, ?, ?)', [
            $dto->userId,
            $dto->currencyId,
            $dto->betId ?? 0,
            'admin_adjust',
            - $dto->amount,
            $dto->comment ?? 'admin_adjust'
        ]);
    }
}