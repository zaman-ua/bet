<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class MinRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return str_starts_with($rule, 'min:');
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        $min = (int) substr($rule, 4);

        if (is_string($value) && mb_strlen($value) < $min) {
            return ValidationRuleResult::failure($value, "Минимум {$min} символов");
        }

        if ((is_int($value) || is_float($value)) && $value < $min) {
            return ValidationRuleResult::failure($value, "Не меньше {$min}");
        }

        return ValidationRuleResult::success($value);
    }
}