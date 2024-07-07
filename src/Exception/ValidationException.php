<?php

namespace Linkedcode\Slim\Exception;

use DomainException;
use JsonSerializable;
use Throwable;

final class ValidationException extends DomainException implements JsonSerializable
{
    private array $errors;

    public function __construct(string $message, array $errors = [], int $code = 422, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function jsonSerialize(): mixed
    {
        return array(
            'errors' => $this->errors
        );
    }
}