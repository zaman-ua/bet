<?php

namespace App\Repository;

use App\Core\Interface\DbInterface;
use App\Domain\MoneyFactory;
use App\DTO\BetCreateDTO;
use App\Interface\BetRepositoryInterface;

final class BetRepository implements BetRepositoryInterface
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
        private readonly DbInterface $db,
    ) {
    }

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

    public function fetchAll() : ?array
    {
        $all = $this->db->getAll('SELECT 
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

    // дублирование кода,
    // но доделываю я на скорую руку, простите
    public function fetchBetsByUserId(int $userId) : ?array
    {
        $all = $this->db->getAll('SELECT 
                b.id,
                b.user_id,
                b.created_at,
                u.name as user_name,
                b.match_id,
                b.outcome,
                b.stake,
                b.coefficient,
                b.currency_id,
                b.status,
                b.payout
            FROM bets as b
            INNER JOIN users as u ON b.user_id = u.id
            WHERE b.user_id = :user_id
            ORDER BY b.id DESC;
        ', ['user_id'=>$userId]);

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
        $row = $this->db->getRow('SELECT 
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
       $item['stake'] = $this->moneyFactory->fromRaw($item['stake'], $item['currency_id']);

       $item['coefficient'] = ($item['coefficient'] / 100);
       $item['payout'] = ($item['payout'] / 100);

        return $item;
    }

    public function processMatches(?array $bets = null, ?array $matches = null) : ?array
    {
        if(!empty($bets) && !empty($matches)) {
            foreach ($bets as $key => $bet) {
                if(isset($matches[$bet['match_id']])) {
                    $bets[$key]['match'] = $matches[$bet['match_id']]['win'] . ' - ' . $matches[$bet['match_id']]['loss'];
                }
            }
        }

        return $bets;
    }
}