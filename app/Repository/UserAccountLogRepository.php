<?php

namespace App\Repository;

use App\Core\Interface\DbInterface;
use App\Domain\MoneyFactory;
use App\DTO\UserAmountLogCreateDTO;
use App\Interface\UserAccountLogRepositoryInterface;

final class UserAccountLogRepository implements UserAccountLogRepositoryInterface
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
        private readonly DbInterface $db,
    ) {
    }

    //      'admin_adjust','bet_place','bet_win',
    //
    //  эти статусы пока остануться без реализации: 'deposit','withdraw','refund'

    public function logBetPlace(UserAmountLogCreateDTO $dto): void
    {
        $this->db->execute('INSERT INTO user_amount_logs (user_id, currency_id, bet_id, type, amount, comment)
             VALUES (?, ?, ?, ?, ?, ?)', [
                 $dto->userId,
                 $dto->currencyId,
                 $dto->betId,
                 'bet_place',
                 - $dto->amount,
                 $dto->comment ?? 'bet_place'
        ]);
    }

    public function logBetWin(UserAmountLogCreateDTO $dto): void
    {
        $this->db->execute('INSERT INTO user_amount_logs (user_id, currency_id, bet_id, type, amount, comment)
             VALUES (?, ?, ?, ?, ?, ?)', [
            $dto->userId,
            $dto->currencyId,
            $dto->betId,
            'bet_win',
            $dto->amount,
            $dto->comment ?? 'bet_win'
        ]);
    }

    public function logAdminAdjust(UserAmountLogCreateDTO $dto): void
    {
        $this->db->execute('INSERT INTO user_amount_logs (user_id, currency_id, bet_id, type, amount, comment)
             VALUES (?, ?, ?, ?, ?, ?)', [
            $dto->userId,
            $dto->currencyId,
            $dto->betId,
            'admin_adjust',
            $dto->amount,
            $dto->comment ?? 'admin_adjust'
        ]);
    }

    public function fetchAll() : ?array
    {
        $all = $this->db->getAll('SELECT 
                ual.*,
                u.name as user_name
            FROM user_amount_logs as ual
            INNER JOIN users as u ON ual.user_id = u.id
            ORDER BY ual.id DESC;
        ');

        if(!empty($all)) {
            return $this->processBets($all);
        }

        return $all;
    }

    private function processBets($items) : array
    {
        foreach ($items as $key => $item) {
            $items[$key]['amount'] = $this->moneyFactory->fromRaw($item['amount'], $item['currency_id']);
        }

        return $items;
    }
}