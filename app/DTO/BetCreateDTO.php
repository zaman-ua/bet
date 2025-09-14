<?php

namespace App\DTO;

use App\Enums\OutcomeEnum;

final class BetCreateDTO
{
    public function __construct(
        public int $userId,
        public int $currencyId,
        public int $matchId,
        public OutcomeEnum $outcome,
        public int $stake,
        public int $coefficient
    ) {}
}