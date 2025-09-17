<?php

namespace App\Repository;

use App\Core\Db\Db;
use App\Domain\Money;
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
        $all = Db::getAll('SELECT 
                b.id,
                b.user_id,
                b.created_at,
                u.name as user_name,
                /*c.code,*/
                b.match_id,
                b.outcome,
                b.stake,
                b.coefficient,
                b.currency_id,
                b.status,
                b.payout
            FROM bets as b
            INNER JOIN users as u ON b.user_id = u.id
            /*INNER JOIN currencies as c ON b.currency_id = c.id*/
            ORDER BY b.id DESC;
        ');

        if(!empty($all)) {
            foreach ($all as $key => $item) {
                $all[$key] = $this->processBets($item);
            }

            return $all;
        }

        return $all;
    }

    public function getById(int $betId) : ?array
    {
        $row = Db::getRow('SELECT 
                b.id,
                b.user_id,
                b.created_at,
                u.name as user_name,
                /*c.code,*/
                b.match_id,
                b.outcome,
                b.stake,
                b.coefficient,
                b.currency_id,
                b.status,
                b.payout
            FROM bets as b
            INNER JOIN users as u ON b.user_id = u.id
            WHERE b.id = :betId
            ORDER BY b.id DESC;
        ', ['betId'=>$betId]);

        if(!empty($row)) {
            return $this->processBets($row);
        }

        return $row;
    }

    private function processBets(array $item) : array
    {
       $item['stake'] = Money::fromRaw($item['stake'], $item['currency_id']);

       $item['coefficient'] = ($item['coefficient'] / 100);
       $item['payout'] = ($item['payout'] / 100);

        return $item;
    }
}