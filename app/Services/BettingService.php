<?php

namespace App\Services;

use App\Core\Interface\DbInterface;
use App\DTO\BetCreateDTO;
use App\DTO\UserAmountLogCreateDTO;
use App\Interface\BetWriterRepositoryInterface;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Interface\UserAmountRepositoryInterface;
use RuntimeException;
use Throwable;

final class BettingService
{
    public function __construct(
        private readonly UserAmountRepositoryInterface     $amounts,
        private readonly BetWriterRepositoryInterface      $betWriterRepository,
        private readonly UserAccountLogRepositoryInterface $userAccountLogs,
        private readonly DbInterface                       $db,
    ) {}

    public function place(BetCreateDTO $dto): int
    {
        try {
            $this->db->begin();

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
            $betId = $this->betWriterRepository->createBet($dto);

            // лог движения
            $this->userAccountLogs->logBetPlace(new UserAmountLogCreateDTO(
                userId: $dto->userId,
                currencyId: $dto->currencyId,
                amount: $dto->stake,
                betId: $betId,
                comment: 'Ставка сделана'
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