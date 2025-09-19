<?php

namespace App\Core\Http;

interface ResponseInterface
{
    public function withStatus(int $code): \App\Core\Http\Response;

    public function withHeader(string $name, string $value): \App\Core\Http\Response;

    public function write(string $body): \App\Core\Http\Response;

    public function status(): int;

    public function headers(): array;

    public function body(): string;
}