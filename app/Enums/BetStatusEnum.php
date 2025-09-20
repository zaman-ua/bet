<?php

namespace App\Enums;

enum BetStatusEnum: string
{
    case Placed = 'placed';
    case Won  = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Placed => 'placed',
            self::Won  => 'Won',
            self::Lost => 'Lost',
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
