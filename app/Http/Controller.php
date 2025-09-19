<?php

namespace App\Http;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;


abstract class Controller
{
    protected array $oldData = [];
    public array $errors = [];

    // применим фишки php8 с автоматическим созданием атрибута
    // через указание в параметрах конструктора
    public function __construct(public RequestInterface $request, public ResponseInterface $response) {
        $this->oldData = $this->request->getPost() ?? [];
    }

    public function html(string $body = '', int $code = 200) : ResponseInterface
    {
        return $this->response
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
//            ->withHeader('X-Content-Type-Options', 'nosniff')
//            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
//            ->withHeader('Content-Security-Policy', "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'")
            ->withStatus($code)
            ->write($body);
    }

    public function json(array $payload = [], int $code = 200) : ResponseInterface
    {
        // сделаем вывод json красивым в Chrome браузере
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($code)
            ->write($body);
    }

    public function redirect(string $url, int $code = 301) : ResponseInterface
    {
        return $this->response
            ->withHeader('HTTP/1.1 301 Moved Permanently','')
            ->withHeader('Location', $url)
            ->withStatus($code)
            ->write('');
    }
}