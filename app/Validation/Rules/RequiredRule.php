<?php

namespace App\Validation\Rules;

use App\Validation\ValidationRuleInterface;
use App\Validation\ValidationRuleResult;

final class RequiredRule implements ValidationRuleInterface
{
    public function supports(string $rule): bool
    {
        return $rule === 'required';
    }

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult
    {
        if ($value === null || $value === '') {
            return ValidationRuleResult::failure($value, 'Обязательное поле');
        }

        return ValidationRuleResult::success($value);
    }
}