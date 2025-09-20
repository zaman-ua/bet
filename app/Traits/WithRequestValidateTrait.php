<?php

namespace App\Traits;

use App\Validation\RequestValidator;
use App\Validation\ValidationRuleInterface;

trait WithRequestValidateTrait
{
    protected function registerValidationRule(ValidationRuleInterface $rule): void
    {
        RequestValidator::registerRuleHandler($rule);
    }

    protected function registerValidationRules(ValidationRuleInterface ...$rules): void
    {
        RequestValidator::registerRuleHandlers(...$rules);
    }

    public function validate(array $data, array $validation) : array
    {
        [$result, $errors] = RequestValidator::validate($data, $validation);

        $this->errors = $errors;
        return $result;
    }
}