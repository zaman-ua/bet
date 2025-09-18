<?php

namespace App\Services;

use App\Core\Db\Db;
use App\DTO\BetCreateDTO;
use App\DTO\UserAmountLogCreateDTO;
use App\Interface\BetRepositoryInterface;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Interface\UserAmountRepositoryInterface;
use RuntimeException;
use Throwable;

final class BettingService
{
    public function __construct(
        private readonly UserAmountRepositoryInterface     $amounts,
        private readonly BetRepositoryInterface            $bets,
        private readonly UserAccountLogRepositoryInterface $userAccountLogs
    ) {}

    public function place(BetCreateDTO $dto): int
    {
        try {
            Db::begin();

            // достаем баланс пользователя с блокировкой на уровне базы для его изменения
            $amount = $this->amounts->lockGet($dto->userId, $dto->currencyId);
            if (!$amount) {
                throw new RuntimeException('balance_not_found');
            }

            // если баланса не хватает - ошибка
            if ((int)$amount['amount'] < $dto->stake) {
                throw new RuntimeException('Не достаточно денег');
            }

            // списание
            $this->amounts->debit($dto->userId, $dto->currencyId, $dto->stake);

            // создание ставки
            $betId = $this->bets->createBet($dto);

            // лог движения
            $this->userAccountLogs->logBetPlace(new UserAmountLogCreateDTO(
                userId: $dto->userId,
                currencyId: $dto->currencyId,
                amount: $dto->stake,
                betId: $betId,
                comment: 'Ставка сделана'
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