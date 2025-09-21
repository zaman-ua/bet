<?php

namespace App\Services\BetPlay;

use App\Interface\BetWriterRepositoryInterface;
use App\Interface\UserAmountRepositoryInterface;

final class WonBetResultHandler implements BetResultHandlerInterface
{
    public function __construct(
        private readonly UserAmountRepositoryInterface $amounts,
        private readonly BetWriterRepositoryInterface $betWriterRepository,
    ) {}

    public function handle(int $betId, array $bet): int
    {
        // считаем выплату
        $stake  = (int)$bet['stake'];
        $coefficient  = (int)$bet['coefficient'] / 100;
        $payout = $stake * $coefficient;

        // вносим в баланс пользователя
        $this->amounts->credit((int)$bet['user_id'], $bet['currency_id'], $payout);

        // отмечаем ставку выигранной и выплату
        $this->betWriterRepository->markWon($betId, $payout);

        return $payout;
    }
}