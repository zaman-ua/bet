<?php

namespace App\Repository;

use App\Core\Interface\DbInterface;
use App\DTO\BetCreateDTO;
use App\Interface\BetWriterRepositoryInterface;

final class BetWriterRepository implements BetWriterRepositoryInterface
{
    public function __construct(
        private readonly DbInterface $db,
    ) {}

    public function createBet(BetCreateDTO $dto): int
    {
        $this->db->execute(
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

        return $this->db->lastInsertId();
    }

    public function lockGet(int $betId): ?array
    {
        return $this->db->getRow('SELECT * FROM bets WHERE id = :id FOR UPDATE', ['id'=>$betId]);
    }

    public function markLost(int $betId): void
    {
        $this->db->execute("UPDATE bets SET status='lost', updated_at=NOW() WHERE id = :id", ['id'=>$betId]);
    }

    public function markWon(int $betId, int $payout): void
    {
        $this->db->execute("UPDATE bets SET 
            status='won', 
            payout=:payout
        WHERE id = :id", [
            'payout' => $payout,
            'id' => $betId
        ]);
    }
}