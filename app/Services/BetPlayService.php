<?php

namespace App\Services;

use App\Core\Db\Db;
use App\DTO\UserAmountLogCreateDTO;
use App\Repository\BetRepository;
use App\Repository\UserAccountLogRepository;
use App\Repository\UserAmountRepository;
use RuntimeException;
use Throwable;

final class BetPlayService
{
    public function __construct(
        protected UserAmountRepository     $amounts = new UserAmountRepository(),
        protected BetRepository            $bets = new BetRepository(),
        protected UserAccountLogRepository $userAccountLogs = new UserAccountLogRepository()
    ) {}

    public function play(int $betId, string $result): int
    {
        try {
            Db::begin();

            // проверяем существование переданного статуса
            if(!in_array($result, ['won','lost'])) {
                throw new RuntimeException('wrong result');
            }

            // достаем ставку с блокировкой на уровне базы для его изменения
            $bet = $this->bets->lockGet($betId);
            if (!$bet) {
                throw new RuntimeException('bet not found');
            }

            // проверяем играла ставка или нет
            if($bet['status'] !== 'placed') {
                throw new RuntimeException('bet is not placed');
            }

            // ставка проиграна, изменений баланса нет
            if($result === 'lost') {
                $this->bets->markLost($betId);
            }

            // ставка выиграна

            // считаем выплату
            $stake  = (int)$bet['stake'];
            $coefficient  = (int)$bet['coefficient'];
            $payout = $stake * $coefficient;

            // вносим в баланс пользователя
            $this->amounts->credit((int)$bet['user_id'], $bet['currency_id'], $payout);

            // отмечаем ставку выигранной и выплату
            $this->bets->markWon($betId, $payout);

            // лог движения
            $this->userAccountLogs->logBetWin(new UserAmountLogCreateDTO(
                userId: $bet['user_id'],
                currencyId: $bet['currency_id'],
                amount: $payout,
                betId: $betId,
                comment: 'Ставка выиграна'
            ));

            Db::commit();
            return $betId;

        } catch (Throwable $e) {
            // откатываем транзакцию
            if(Db::inTransaction()) {
                Db::rollBack();
            }

            // пробрасываем исключение далее
            throw $e;
        }
    }
}