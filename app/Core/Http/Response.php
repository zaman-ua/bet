<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Response
{
    private int $status = 200;
    private array $headers = ['Content-Type' => 'text/html; charset=utf-8'];
    private string $body = '';

    public function withStatus(int $code) : self
    {
        // PSR-7 иммутабельность (после создания их нельзя изменять)
        $clone = clone $this;
        $clone->status = $code;
        return $clone;
    }

    public function withHeader(string $name, string $value) : self
    {
        // PSR-7 иммутабельность (после создания их нельзя изменять)
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function write(string $body) : self
    {
        $this->body = $body;
        return $this;
    }

    public function status() : int
    {
        return $this->status;
    }
    public function headers() : array
    {
        return $this->headers;
    }
    public function body() : string
    {
        return $this->body;
    }
}
