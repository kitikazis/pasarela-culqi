<?php

namespace App\Exceptions;

use RuntimeException;

class CulqiException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $httpStatus = 400,
        private readonly ?array $culqiError = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getCulqiError(): ?array
    {
        return $this->culqiError;
    }
}