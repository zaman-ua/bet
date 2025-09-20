<?php

namespace App\Services;

use App\Core\Interface\DbInterface;
use App\DTO\UserAmountLogCreateDTO;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Interface\UserAmountRepositoryInterface;
use Throwable;

final class AmountService
{
    public function __construct(
        private UserAmountRepositoryInterface     $amounts,
        private UserAccountLogRepositoryInterface $userAccountLogs,
        private readonly DbInterface $db,
    ) {}

    public function adjust(int $userId, int $currencyId, int $amount, string $comment = ''): void
    {
        if ($amount === 0) return;

        try {
            $this->db->begin();

            if ($amount > 0) {
                $this->amounts->credit($userId, $currencyId, $amount);
            } else {
                // минус на минус даст плюс и будет все ок по логике
                $this->amounts->debit($userId, $currencyId, -$amount);
            }
            $this->userAccountLogs->logAdminAdjust(new UserAmountLogCreateDTO(
                userId: $userId,
                currencyId: $currencyId,
                amount: $amount,
                comment: $comment
            ));

            $this->db->commit();

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