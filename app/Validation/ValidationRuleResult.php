<?php

namespace App\Validation;

final class ValidationRuleResult
{
    public function __construct(
        private readonly mixed $value,
        private readonly ?string $error = null,
        private readonly bool $shouldStop = false,
    ) {
    }

    public static function success(mixed $value, bool $shouldStop = false): self
    {
        return new self($value, null, $shouldStop);
    }

    public static function failure(mixed $value, string $error): self
    {
        return new self($value, $error, true);
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    public function shouldStop(): bool
    {
        return $this->shouldStop;
    }
}