<?php

namespace App\Services\BetPlay;

interface BetResultHandlerInterface
{
    // Выполняет обновление ставки и возвращает сумму выплаты.
    public function handle(int $betId, array $bet): int;
}