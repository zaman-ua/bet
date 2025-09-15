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
                $dto->outcome->value,
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
            payout=:payout
        WHERE id = :id", [
            'payout' => $payout,
            'id' => $betId
        ]);
    }

    public function fetchAll() : ?array
    {
        return Db::getAll('SELECT 
                b.created_at,
                u.name,
                c.code,
                b.match_id,
                b.outcome,
                b.stake,
                b.coefficient,
                b.status,
                b.payout
            FROM bets as b
            INNER JOIN users as u ON b.user_id = u.id
            INNER JOIN currencies as c ON b.currency_id = c.id
        ');
    }
}