<?php

namespace App\Domain;

use RuntimeException;

final class Money
{
    public readonly int $amount;
    public readonly int $currencyId;
    public readonly string $symbol;
    private static ?MoneyFactory $factory = null;

    public function __construct(int $amount, int $currencyId, string $symbol)
    {
        $this->amount = $amount;
        $this->currencyId = $currencyId;

//        $this->symbol = (new CurrencyRepository()->getSymbolById($currencyId));
        $this->symbol = $symbol;
    }

    public static function setFactory(MoneyFactory $factory): void
    {
        self::$factory = $factory;
    }

    private static function factory(): MoneyFactory
    {
        if (self::$factory === null) {
            throw new RuntimeException('Money factory is not configured');
        }

        return self::$factory;
    }

    // с обычного числа в значение для базы
    public static function fromHuman(string $amount, int $currencyId): self
    {
//        // приходит строкой с формы или откуда-то еще
//        // вдруг формат с пробелами на тысячах
//        $amount = trim(str_replace(' ', '', $amount));
//
//        // стандартно меняем запятую на точку
//        $amount = str_replace(',', '.', $amount);
//
//        // проверяем похоже ли оно на число
//        if ($amount === '' || !preg_match('/^-?\d+(\.\d{1,2})?$/', $amount)) {
//            throw new \InvalidArgumentException("Invalid money: '{$amount}'");
//        }
//
//        // приводим к целочисленному умножено на 100
//        // простой вариант, по хорошему нужно более сложный путь
//        $amount = (int) ((float)$amount * 100);
//
//        return new self($amount, $currencyId);

        return self::factory()->fromHuman($amount, $currencyId);
    }

    // со значения в базе с кодом или ид валюты
    public static function fromRaw(int $amount, ?int $currencyId = null, ?string $currencyCode = null) : self
    {
//        if(!empty($currencyId)) {
//            return new self($amount, $currencyId);
//
//        } else if(!empty($currencyCode)) {
//            $currencyId = (new CurrencyRepository()->getIdByCode($currencyCode));
//            return new self($amount, $currencyId);
//        }
//
//        throw new RuntimeException('Invalid currency code or id');

        return self::factory()->fromRaw($amount, $currencyId, $currencyCode);
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
