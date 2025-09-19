<?php

namespace App\Interface;

use App\DTO\BetCreateDTO;

interface BetRepositoryInterface
{
    public function createBet(BetCreateDTO $dto): int;

    public function lockGet(int $betId): ?array;

    public function markLost(int $betId): void;

    public function markWon(int $betId, int $payout): void;

    public function fetchAll() : ?array;

    public function fetchBetsByUserId(int $userId) : ?array;

    public function getById(int $betId) : ?array;

    public function processMatches(?array $bets = null, ?array $matches = null) : ?array;
}