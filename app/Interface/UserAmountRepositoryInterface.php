<?php

namespace App\Interface;

interface UserAmountRepositoryInterface
{
    public function lockGet(int $userId, int $currencyId): ?array;

    public function debit(int $userId, int $currencyId, int $amount): void;

    public function credit(int $userId, int $currencyId, int $amount): void;
}