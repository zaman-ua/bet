<?php

namespace App\DTO;

use App\Domain\Money;

final class BetViewDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly string $created_at,
        public readonly string $user_name,
        public readonly int $match_id,
        public readonly string $outcome,
        public readonly Money $stake,
        public readonly float $coefficient,
        public readonly int $currency_id,
        public readonly string $status,
        public readonly Money $payout,
        public readonly ?string $match = null,
    ) {}

    public function withMatch(?string $match): self
    {
        return new self(
            id: $this->id,
            user_id: $this->user_id,
            created_at: $this->created_at,
            user_name: $this->user_name,
            match_id: $this->match_id,
            outcome: $this->outcome,
            stake: $this->stake,
            coefficient: $this->coefficient,
            currency_id: $this->currency_id,
            status: $this->status,
            payout: $this->payout,
            match: $match,
        );
    }
}