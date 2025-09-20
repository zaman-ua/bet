<?php

namespace App\Repository;

use App\Core\Interface\DbInterface;
use App\Domain\MoneyFactory;
use App\DTO\BetViewDTO;
use App\Interface\BetReaderRepositoryInterface;

final class BetReaderRepository implements BetReaderRepositoryInterface
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
        private readonly DbInterface $db,
    ) {}

    public function fetchAll() : array
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

        if(empty($all)) {
            return [];
        }

        return array_map(fn (array $item): BetViewDTO => $this->hydrateBet($item), $all);
    }

    // дублирование кода,
    // но доделываю я на скорую руку, простите
    public function fetchBetsByUserId(int $userId) : array
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

        if (empty($all)) {
            return [];
        }

        return array_map(fn (array $item): BetViewDTO => $this->hydrateBet($item), $all);
    }

    public function getById(int $betId) : ?BetViewDTO
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

        if (empty($row)) {
            return null;
        }

        return $this->hydrateBet($row);
    }

    private function hydrateBet(array $item): BetViewDTO
    {
        $stake = $this->moneyFactory->fromRaw((int) $item['stake'], (int) $item['currency_id']);
        $payout = $this->moneyFactory->fromRaw((int) $item['payout'], (int) $item['currency_id']);

        return new BetViewDTO(
            id: (int) $item['id'],
            user_id: (int) $item['user_id'],
            created_at: (string) $item['created_at'],
            user_name: (string) $item['user_name'],
            match_id: (int) $item['match_id'],
            outcome: (string) $item['outcome'],
            stake: $stake,
            coefficient: ((int) $item['coefficient']) / 100,
            currency_id: (int) $item['currency_id'],
            status: (string) $item['status'],
            payout: $payout,
        );
    }

}