<?php

namespace App\Api;


use App\Core\Http\Request;
use App\Core\Http\Response;

abstract class ApiController
{
    public function __construct(public Request $request, public Response $response) {}
    public function json(array $payload = []) : Response
    {
        // сделаем вывод json красивым в Chrome браузере
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200)
            ->write($body);
    }
}