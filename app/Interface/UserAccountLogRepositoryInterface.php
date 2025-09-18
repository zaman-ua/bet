<?php

namespace App\Interface;

use App\DTO\UserAmountLogCreateDTO;

interface UserAccountLogRepositoryInterface
{
    public function logBetPlace(UserAmountLogCreateDTO $dto): void;

    public function logBetWin(UserAmountLogCreateDTO $dto): void;

    public function logAdminAdjust(UserAmountLogCreateDTO $dto): void;

    public function fetchAll() : ?array;
}