<?php

namespace App\Enums;

enum OutcomeEnum: string
{
    case Win  = 'win';
    case Draw = 'draw';
    case Loss = 'loss';

    public function label(): string
    {
        return match ($this) {
            self::Win  => 'Win',
            self::Draw => 'Draw',
            self::Loss => 'Loss',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(?string $v): bool
    {
        return $v !== null && self::tryFrom($v) !== null;
    }
}

//    Пример использования:
//
//    $outcome = OutcomeEnum::from('win');     // OutcomeEnum::Win
//    if (OutcomeEnum::isValid($_POST['outcome'] ?? null)) {
//        $o = OutcomeEnum::from($_POST['outcome']);
//        echo $o->label();                    // "Win"
//    }
