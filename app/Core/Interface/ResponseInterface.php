<?php

namespace App\Core\Interface;

interface ResponseInterface
{
    public function withStatus(int $code): ResponseInterface;

    public function withHeader(string $name, string $value): ResponseInterface;

    public function write(string $body): ResponseInterface;

    public function status(): int;

    public function headers(): array;

    public function body(): string;
}