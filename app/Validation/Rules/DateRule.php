<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class DateRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return str_starts_with($rule, 'date:') || $rule === 'date';
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        $format = 'Y-m-d';

        if ($rule !== 'date') {
            $format = substr($rule, 5) ?: $format;
        }

        $date = \DateTime::createFromFormat($format, (string) $value);

        if (!$date || $date->format($format) !== (string) $value) {
            return ValidationRuleResult::failure($value, 'Некорректная дата');
        }

        return ValidationRuleResult::success($value);
    }
}