<?php

namespace App\Repository;

use App\Core\Db\Db;
use App\DTO\BetCreateDTO;

final class BetRepository
{
    public function createBet(BetCreateDTO $dto): int
    {
        Db::execute(
            "INSERT INTO bets (user_id, currency_id, match_id, outcome, stake, coefficient, status)
             VALUES (?, ?, ?, ?, ?, ?, 'placed')",
            [
                $dto->userId,
                $dto->currencyId,
                $dto->matchId,
                $dto->outcome,
                $dto->stake,
                $dto->coefficient
            ]);

        return Db::lastInsertId();
    }

    public function lockGet(int $betId): ?array
    {
        return Db::getRow('SELECT * FROM bets WHERE id = :id FOR UPDATE', ['id'=>$betId]);
    }

    public function markLost(int $betId): void
    {
        Db::execute("UPDATE bets SET status='lost', updated_at=NOW() WHERE id = :id", ['id'=>$betId]);
    }

    public function markWon(int $betId, int $payout): void
    {
        Db::execute("UPDATE bets SET 
            status='won', 
            payout=:payout, 
            updated_at=NOW()
        WHERE id = :id", [
            'payout' => $payout,
            'id' => $betId
        ]);
    }
}