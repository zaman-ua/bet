<?php

namespace App\Validation;

final class RequestValidator
{
    /** @var ValidationRuleInterface[] */
    private static array $ruleHandlers = [];

    public static function registerRuleHandler(ValidationRuleInterface $handler): void
    {
        self::$ruleHandlers[$handler::class] = $handler;
    }

    public static function registerRuleHandlers(ValidationRuleInterface ...$handlers): void
    {
        foreach ($handlers as $handler) {
            self::registerRuleHandler($handler);
        }
    }

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
        return array_values(self::$ruleHandlers);
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