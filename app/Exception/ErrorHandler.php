<?php

namespace App\Exception;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use Throwable;


final class ErrorHandler
{
    public function __construct(private bool $debug = false) {}
    public function render(Throwable $e, RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // логируем (минимально)
        error_log(sprintf('[%s] %s: %s in %s:%d',
            'ERROR', $e::class, $e->getMessage(), $e->getFile(), $e->getLine()
        ));

        if ($request->wantsJson()) {
            $payload = [
                'error' => [
                    'status' => 500,
                    'message' => $this->getMessage($e)
                ]
            ];
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->write($body);
        }

        $body = $this->getMessage($e);

        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->write($body);
    }

    private function getMessage(Throwable $e)
    {
        return $this->debug ? $e->getMessage() . ': ' . $e->getFile() . ' [' . $e->getLine() .']' : $e->getMessage();
    }

}