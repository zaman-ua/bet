<?php

namespace App\Exception;

use LogicException;
final class TwigRenderException extends LogicException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}