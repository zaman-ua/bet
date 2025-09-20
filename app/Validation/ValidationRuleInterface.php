<?php

namespace App\Validation;

interface ValidationRuleInterface
{
    public function supports(string $rule): bool;

    public function validate(string $field, mixed $value, string $rule, array $data): ValidationRuleResult;
}