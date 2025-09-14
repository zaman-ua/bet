<?php

namespace App\Api;


use App\Core\Http\Request;
use App\Core\Http\Response;

abstract class ApiController
{
    protected array $oldData = [];
    public array $errors = [];

    // применим фишки php8 с автоматическим созданием атрибута
    // через указание в параметрах конструктора
    public function __construct(public Request $request, public Response $response) {
        $this->oldData = $this->request->post ?? [];
    }
    public function json(array $payload = [], int $code = 200) : Response
    {
        // сделаем вывод json красивым в Chrome браузере
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($code)
            ->write($body);
    }

    public function validate(array $data, array $validation) : array
    {
        $errors = [];
        $result = [];

        // валидируемся только когда не пустые данные и правила
        if(!empty($data) && !empty($validation)) {
            foreach ($validation as $field => $rules) {

                $value = $data[$field] ?? null;
                $value = trim($value);

                $nullable = in_array('nullable', $rules, true);
                $required = in_array('required', $rules, true);

                if (!$nullable && $required && ($value === null || $value === '')) {
                    $errors[$field] = 'Обязательное поле';
                    continue;
                }

                if ($nullable && ($value === null || $value === '')) {
                    $result[$field] = null;
                    continue;
                }

                foreach ($rules as $rule) {
                    if (in_array($rule, ['required','nullable','string'], true)) {
                        if ($rule === 'string' && !is_string($value)) {
                            $errors[$field] = 'Должно быть строкой'; break;
                        }
                        continue;
                    }

                    if ($rule === 'email') {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = 'Некорректный email'; break;
                        }
                    } elseif ($rule === 'int') {
                        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                            $errors[$field] = 'Должно быть целым числом'; break;
                        }
                        $value = (int)$value;
                    } elseif ($rule === 'boolean') {
                        $value = !empty($value);

                    } elseif (str_starts_with($rule, 'min:')) {
                        $min = (int)substr($rule, 4);
                        if (is_string($value) && mb_strlen($value) < $min) {
                            $errors[$field] = "Минимум {$min} символов"; break;
                        }
                        if (is_int($value) && $value < $min) {
                            $errors[$field] = "Не меньше {$min}"; break;
                        }
                    } elseif (str_starts_with($rule, 'max:')) {
                        $max = (int)substr($rule, 4);
                        if (is_string($value) && mb_strlen($value) > $max) {
                            $errors[$field] = "Максимум {$max} символов"; break;
                        }
                        if ((is_int($value) || is_float($value)) && $value > $max) {
                            $errors[$field] = "Не больше {$max}"; break;
                        }
                    } elseif (str_starts_with($rule, 'in:')) {
                        $allowed = explode(',', substr($rule, 3));
                        if (!in_array((string)$value, $allowed, true)) {
                            $errors[$field] = 'Недопустимое значение'; break;
                        }
                    } elseif (str_starts_with($rule, 'date:')) {
                        $fmt = substr($rule, 5) ?: 'Y-m-d';
                        $dt  = \DateTime::createFromFormat($fmt, (string)$value);
                        if (!$dt || $dt->format($fmt) !== $value) {
                            $errors[$field] = 'Некорректная дата'; break;
                        }
                    }
                }

                if (!isset($errors[$field])) {
                    $result[$field] = $value;
                }
            }
        }

        $this->errors = $errors;
        return $result;
    }
}