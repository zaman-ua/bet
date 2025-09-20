<?php

namespace App\Interface;

use App\DTO\BetCreateDTO;
use App\DTO\BetViewDTO;

interface BetWriterRepositoryInterface
{
    public function createBet(BetCreateDTO $dto): int;

    public function lockGet(int $betId): ?array;

    public function markLost(int $betId): void;

    public function markWon(int $betId, int $payout): void;

}