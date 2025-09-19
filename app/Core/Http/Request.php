<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Interface\RequestInterface;

final class Request implements RequestInterface
{
    // соберем все что есть из глобальных переменных
    public function __construct(
        private string $method,
        private string $uri,
        private string $path,
        private array $query,
        private array $post,
        private array $files,
        private array $cookies,
        private array $headers,
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

    public function getMethod(): string
    {
        return $this->method;
    }
    public function getUri(): string
    {
        return $this->uri;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getQuery(): array
    {
        return $this->query;
    }
    public function getPost(): array
    {
        return $this->post;
    }
    public function getFiles(): array
    {
        return $this->files;
    }
    public function getCookies(): array
    {
        return $this->cookies;
    }
    public function getHeaders(): array
    {
        return $this->headers;
    }
}