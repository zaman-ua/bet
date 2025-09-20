<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class EmailRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return $rule === 'email';
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ValidationRuleResult::failure($value, 'Некорректный email');
        }

        return ValidationRuleResult::success($value);
    }
}