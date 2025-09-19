<?php

namespace App\Core\Http;

interface RequestInterface
{
    public function withAttribute(string $key, mixed $value): \App\Core\Http\Request;

    public function getAttribute(string $key, mixed $default = null): mixed;

    public function wantsJson(): bool;
}