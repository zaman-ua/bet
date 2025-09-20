<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class InRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return str_starts_with($rule, 'in:');
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        $allowed = explode(',', substr($rule, 3));

        if (!in_array((string) $value, $allowed, true)) {
            return ValidationRuleResult::failure($value, 'Недопустимое значение');
        }

        return ValidationRuleResult::success($value);
    }
}