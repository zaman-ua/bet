<?php

namespace App\Domain;

final class Money
{
    public readonly int $amount;
    public readonly int $currencyId;

    public function __construct(int $amount, int $currencyId)
    {
        $this->amount = $amount;
        $this->currencyId = $currencyId;
    }

    /** "150.00", "150", "150,5" → Money; без float */
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

    /** "150.00" */
    public function toHuman(): string
    {
        $amount = $this->amount / 100;

        return number_format($amount, 2, '.', '');
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
