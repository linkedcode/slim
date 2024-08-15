<?php

namespace Linkedcode\Slim\Validator;

use Linkedcode\Slim\Exception\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;

class AbstractRequestValidator
{
    private array $violations = [];

    public function getViolations(): array
    {
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return !empty($this->violations);
    }

    public function checkViolations()
    {
        if ($this->hasViolations()) {
            throw new ValidationException("Unprocessable Entity", $this->violations);
        }
    }

    protected function validateField($value, $fieldValidator, string $fieldName)
    {
        try {
            $fieldValidator->assert($value);
        } catch (NestedValidationException $exception) {
            $this->violations[$fieldName] = $exception->getMessages();
        }
    }
}