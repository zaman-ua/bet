<?php

namespace App\Domain;

use App\Repository\CurrencyRepository;
use RuntimeException;

final class Money
{
    public readonly int $amount;
    public readonly int $currencyId;
    public readonly string $symbol;

    public function __construct(int $amount, int $currencyId)
    {
        $this->amount = $amount;
        $this->currencyId = $currencyId;

        $this->symbol = (new CurrencyRepository()->getSymbolById($currencyId));
    }

    // с обычного числа в значение для базы
    public static function fromHuman(string $amount, int $currencyId): self
    {
        // приходит строкой с формы или откуда-то еще
        // вдруг формат с пробелами на тысячах
        $amount = trim(str_replace(' ', '', $amount));

        // стандартно меняем запятую на точку
        $amount = str_replace(',', '.', $amount);

        // проверяем похоже ли оно на число
        if ($amount === '' || !preg_match('/^-?\d+(\.\d{1,2})?$/', $amount)) {
            throw new \InvalidArgumentException("Invalid money: '{$amount}'");
        }

        // приводим к целочисленному умножено на 100
        // простой вариант, по хорошему нужно более сложный путь
        $amount = (int) ((float)$amount * 100);

        return new self($amount, $currencyId);
    }

    // со значения в базе с кодом или ид валюты
    public static function fromRaw(int $amount, ?int $currencyId = null, ?string $currencyCode = null) : self
    {
        if(!empty($currencyId)) {
            return new self($amount, $currencyId);

        } else if(!empty($currencyCode)) {
            $currencyId = (new CurrencyRepository()->getIdByCode($currencyCode));
            return new self($amount, $currencyId);
        }

        throw new RuntimeException('Invalid currency code or id');
    }

    // со значения в базе, в человеку понятное
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
        return new self($this->amount + $other->amount, $this->currencyId);
    }

    public function sub(self $other): self
    {
        $this->sameCurrency($other);
        return new self($this->amount - $other->amount, $this->currencyId);
    }

    private function sameCurrency(self $other): void
    {
        if ($this->currencyId !== $other->currencyId) {
            throw new \InvalidArgumentException('Currency mismatch');
        }
    }
}
