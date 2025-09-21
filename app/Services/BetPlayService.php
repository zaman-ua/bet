<?php

namespace App\Services;

use App\Core\Interface\DbInterface;
use App\DTO\UserAmountLogCreateDTO;
use App\Enums\BetStatusEnum;
use App\Interface\BetWriterRepositoryInterface;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Interface\UserAmountRepositoryInterface;
use App\Services\BetPlay\BetResultHandlerInterface;
use RuntimeException;
use Throwable;

final class BetPlayService
{
    public function __construct(
        protected BetWriterRepositoryInterface      $betWriterRepository,
        protected UserAccountLogRepositoryInterface $userAccountLogs,
        private readonly DbInterface                $db,
        private readonly array                      $resultHandlers,
    ) {}

    public function play(int $betId, BetStatusEnum $betPlayEnum): int
    {
        try {
            $this->db->begin();

            // достаем ставку с блокировкой на уровне базы для его изменения
            $bet = $this->betWriterRepository->lockGet($betId);
            if (!$bet) {
                throw new RuntimeException('bet not found');
            }

            // проверяем играла ставка или нет
            // с Enum стало как то странно
            // наверное нужно разбить статус в базе на два поля: играла/нет, выиграла/проиграла
            if(BetStatusEnum::from($bet['status']) !== BetStatusEnum::Placed) {
                throw new RuntimeException('bet is not placed');
            }

            // вынос логики игры ставки
            // подключается в контейнере
            $handler = $this->resultHandlers[$betPlayEnum->value] ?? null;
            if (!$handler instanceof BetResultHandlerInterface) {
                throw new RuntimeException(sprintf('handler for status %s not found', $betPlayEnum->value));
            }
            $payout = $handler->handle($betId, $bet);

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