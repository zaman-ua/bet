<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class IntRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return $rule === 'int';
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return ValidationRuleResult::failure($value, 'Должно быть целым числом');
        }

        return ValidationRuleResult::success((int) $value);
    }
}