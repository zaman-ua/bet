<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class StringRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return $rule === 'string';
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        if (!is_string($value)) {
            return ValidationRuleResult::failure($value, 'Должно быть строкой');
        }

        return ValidationRuleResult::success($value);
    }
}