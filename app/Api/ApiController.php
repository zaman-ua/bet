<?php

namespace App\Api;


use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Validation\RequestValidator;

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
        [$result, $errors] = RequestValidator::validate($data, $validation);

        $this->errors = $errors;
        return $result;
    }
}