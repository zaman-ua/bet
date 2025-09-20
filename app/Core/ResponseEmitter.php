<?php

namespace App\Core;

use App\Core\Interface\ResponseInterface;

final class ResponseEmitter
{
    public function emit(ResponseInterface $response) : void
    {
        // устанавливаем правильный код ответа
        http_response_code($response->status());

        // устанавливаем заголовки
        foreach ($response->headers() as $name => $value) {
            header("$name: $value", true);
        }

        // отображаем контент
        echo $response->body();
    }
}