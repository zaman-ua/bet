<?php

namespace App\DTO;

final class UserAmountLogCreateDTO
{
    public function __construct(
        public int $userId,
        public int $currencyId,
        public int $amount,
        public ?int $betId = null,
        public string $comment = ''
    ) {}
}