<?php

namespace App\Services;

use App\Core\Interface\DbInterface;
use App\DTO\UserAmountLogCreateDTO;
use App\Enums\BetStatusEnum;
use App\Interface\BetRepositoryInterface;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Interface\UserAmountRepositoryInterface;
use RuntimeException;
use Throwable;

final class BetPlayService
{
    public function __construct(
        protected UserAmountRepositoryInterface     $amounts,
        protected BetRepositoryInterface            $bets,
        protected UserAccountLogRepositoryInterface $userAccountLogs,
        private readonly DbInterface                $db,
    ) {}

    public function play(int $betId, BetStatusEnum $betPlayEnum): int
    {
        try {
            $this->db->begin();

            // достаем ставку с блокировкой на уровне базы для его изменения
            $bet = $this->bets->lockGet($betId);
            if (!$bet) {
                throw new RuntimeException('bet not found');
            }

            // проверяем играла ставка или нет
            // с Enum стало как то странно
            // наверное нужно разбить статус в базе на два поля: играла/нет, выиграла/проиграла
            if(BetStatusEnum::from($bet['status']) !== BetStatusEnum::Placed) {
                throw new RuntimeException('bet is not placed');
            }

            // ставка проиграна, изменений баланса нет
            if($betPlayEnum === BetStatusEnum::Lost) {
                $this->bets->markLost($betId);
                $payout = 0;
            }

            // ставка выиграна
            if($betPlayEnum === BetStatusEnum::Won) {
                // считаем выплату
                $stake  = (int)$bet['stake'];
                $coefficient  = (int)$bet['coefficient'] / 100;
                $payout = $stake * $coefficient;

                // вносим в баланс пользователя
                $this->amounts->credit((int)$bet['user_id'], $bet['currency_id'], $payout);

                // отмечаем ставку выигранной и выплату
                $this->bets->markWon($betId, $payout);
            }

            // лог движения
            $this->userAccountLogs->logBetWin(new UserAmountLogCreateDTO(
                userId: $bet['user_id'],
                currencyId: $bet['currency_id'],
                amount: $payout,
                betId: $betId,
                comment: 'Ставка ' . $betPlayEnum->value
            ));

            $this->db->commit();
            return $betId;

        } catch (Throwable $e) {
            // откатываем транзакцию
            if($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            // пробрасываем исключение далее
            throw $e;
        }
    }
}