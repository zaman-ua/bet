<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class MaxRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return str_starts_with($rule, 'max:');
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        $max = (int) substr($rule, 4);

        if (is_string($value) && mb_strlen($value) > $max) {
            return ValidationRuleResult::failure($value, "Максимум {$max} символов");
        }

        if ((is_int($value) || is_float($value)) && $value > $max) {
            return ValidationRuleResult::failure($value, "Не больше {$max}");
        }

        return ValidationRuleResult::success($value);
    }
}