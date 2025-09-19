<?php

namespace App\Domain;

final class Money
{
    public readonly int $amount;
    public readonly int $currencyId;
    public readonly string $symbol;

    public function __construct(int $amount, int $currencyId, string $symbol)
    {
        $this->amount = $amount;
        $this->currencyId = $currencyId;
        $this->symbol = $symbol;
    }

    // со значения в базе, в человеко-понятное
    public function toHuman(bool $withSymbol = false): string
    {
        $amount = $this->amount / 100;

        return number_format($amount, 2, '.', '') . ($withSymbol ? ' ' . $this->symbol : '');
    }

    // должно автоматом сработать когда в шаблоне буду выводить
    public function __toString() : string
    {
        return $this->toHuman(true);
    }

    public function add(self $other): self
    {
        $this->sameCurrency($other);
        return new self($this->amount + $other->amount, $this->currencyId, $this->symbol);
    }

    public function sub(self $other): self
    {
        $this->sameCurrency($other);
        return new self($this->amount - $other->amount, $this->currencyId, $this->symbol);
    }

    private function sameCurrency(self $other): void
    {
        if ($this->currencyId !== $other->currencyId) {
            throw new \InvalidArgumentException('Currency mismatch');
        }
    }
}
