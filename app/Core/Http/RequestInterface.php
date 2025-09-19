<?php

namespace App\Core\Http;

interface RequestInterface
{
    public function withAttribute(string $key, mixed $value): RequestInterface;

    public function getAttribute(string $key, mixed $default = null): mixed;

    public function wantsJson(): bool;

    public function getMethod(): string;
    public function getUri(): string;
    public function getPath(): string;
    public function getQuery(): array;
    public function getPost(): array;
    public function getFiles(): array;
    public function getCookies(): array;
    public function getHeaders(): array;
}