<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class BooleanRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return $rule === 'boolean';
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        return ValidationRuleResult::success(!empty($value));
    }
}