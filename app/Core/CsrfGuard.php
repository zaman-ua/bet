<?php

namespace App\Core;

use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;

final class CsrfGuard
{
    public function validate(RequestInterface $request, ResponseInterface $response) : ?ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return null;
        }

        $token = $request->getPost()['csrf'] ?? '';
        $sessionToken = $_SESSION['csrf'] ?? '';

        if ($token === $sessionToken) {
            return null;
        }

        return $response
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withStatus(419)
            ->write('CSRF token mismatch');
    }
}