<?php

namespace App\Exception;

use LogicException;
final class ConfigurationException extends LogicException
{
    public function __construct(
        string $message = 'No database configuration found',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}