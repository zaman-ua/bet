<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class NullableRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return $rule === 'nullable';
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        if ($value === null || $value === '') {
            return ValidationRuleResult::success(null, true);
        }

        return ValidationRuleResult::success($value);
    }
}