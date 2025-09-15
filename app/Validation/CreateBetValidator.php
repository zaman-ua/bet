<?php

namespace App\Validation;

use App\Domain\Money;
use App\Enums\OutcomeEnum;

final class CreateBetValidator
{
    public static function outcome(string $outcome): void
    {
        // дергаем валидность с самого enum
        if (!OutcomeEnum::isValid($outcome)) {
            throw new \InvalidArgumentException('Invalid outcome');
        }
    }

    public static function coefficientValidate(string $coefficient, array $config): int
    {
        // приходит строкой с формы или откуда-то еще
        // стандартно меняем запятую на точку
        $coefficient = trim(str_replace(',', '.', $coefficient));

        // проверяем что это все похоже на дробное число
        if (!preg_match('/^\d{1,2}(\.\d{1,2})?$/', $coefficient)) {
            throw new \InvalidArgumentException('Invalid coefficient format');
        }

        // приводим к целочисленному умножено на 100
        // простой вариант, по хорошему нужно более сложный путь
        $result = (int) ((float)$coefficient * 100);
        
        if ($result < $config['min_coefficient'] || $result > $config['max_coefficient']) {
            throw new \InvalidArgumentException('coefficient out of range ['.($config['min_coefficient'] / 100).'..'.($config['max_coefficient'] / 100).']');
        }
        return $result;
    }

    public static function stakeValidate(string $amount, int $currencyId, array $config): int
    {
        $money = Money::fromHuman($amount, $currencyId);

        if ($money->amount < $config['min_bet'] || $money->amount > $config['max_bet']) {
            throw new \InvalidArgumentException('Stake must be within '.($config['min_bet'] / 100).'..'.($config['max_bet'] / 100) );
        }
        return $money->amount;
    }
}