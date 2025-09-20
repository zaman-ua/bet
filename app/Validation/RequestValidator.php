<?php

namespace App\Validation;

use App\Validation\Rules\BooleanRule;
use App\Validation\Rules\DateRule;
use App\Validation\Rules\EmailRule;
use App\Validation\Rules\InRule;
use App\Validation\Rules\IntRule;
use App\Validation\Rules\MaxRule;
use App\Validation\Rules\MinRule;
use App\Validation\Rules\NullableRule;
use App\Validation\Rules\RequiredRule;
use App\Validation\Rules\StringRule;

final class RequestValidator
{
    /** @var ValidationRuleInterface[] */
    private static array $ruleHandlers = [];

    public static function validate(array $data, array $validation) : array
    {
        $errors = [];
        $result = [];

        // валидируемся только когда не пустые данные и правила
        if(!empty($data) && !empty($validation)) {
            foreach ($validation as $field => $rules) {

                $value = $data[$field] ?? null;

                if (is_string($value)) {
                    $value = trim($value);
                }

                foreach ($rules as $rule) {
                    $handler = self::resolveRule($rule);
                    if ($handler === null) {
                        continue;
                    }

                    $outcome = $handler->validate($field, $value, $rule, $data);
                    $value = $outcome->value();

                    if ($outcome->error() !== null) {
                        $errors[$field] = $outcome->error();
                        break;
                    }

                    if ($outcome->shouldStop()) {
                        break;
                    }
                }

                if (!isset($errors[$field])) {
                    $result[$field] = $value;
                }
            }
        }

        return [
            $result,
            $errors
        ];
    }

    /**
     * @return ValidationRuleInterface[]
     */
    private static function rules(): array
    {
        if (self::$ruleHandlers === []) {
            self::$ruleHandlers = [
                new NullableRule(),
                new RequiredRule(),
                new StringRule(),
                new EmailRule(),
                new IntRule(),
                new BooleanRule(),
                new MinRule(),
                new MaxRule(),
                new InRule(),
                new DateRule(),
            ];
        }

        return self::$ruleHandlers;
    }

    private static function resolveRule(string $rule): ?ValidationRuleInterface
    {
        foreach (self::rules() as $handler) {
            if ($handler->supports($rule)) {
                return $handler;
            }
        }

        return null;
    }
}