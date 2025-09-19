<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Request implements RequestInterface
{
    // соберем все что есть из глобальных переменных
    public function __construct(
        public string $method,
        public string $uri,
        public string $path,
        public array $query,
        public array $post,
        public array $files,
        public array $cookies,
        public array $headers,
        private array $attributes = []
    ) {}

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $path   = explode('?', $uri, 2)[0] ?: '/';

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            // берем только HTTP_ заголовки
            if (str_starts_with($key, 'HTTP_')) {
                // преобразуем к их стандартному виду
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }
        return new self($method, $uri, rawurldecode($path), $_GET, $_POST, $_FILES, $_COOKIE, $headers);
    }

    public function withAttribute(string $key, mixed $value): self
    {
        // PSR-7 иммутабельность (после создания их нельзя изменять)
        $clone = clone $this;
        $clone->attributes[$key] = $value;
        return $clone;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function wantsJson(): bool
    {
        $accept = $this->headers['Accept'] ?? '';
        $contentType = $this->headers['Content-Type'] ?? '';
        $xRequestWith = $this->headers['X-Requested-With'] ?? '';
        return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json') || $xRequestWith === 'XMLHttpRequest';
    }
}