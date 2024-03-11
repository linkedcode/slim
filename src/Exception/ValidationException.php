<?php

namespace Linkedcode\Slim\Exception;

use Exception;

class ValidationException extends Exception
{
    protected $errors = [];

    protected $code = 400;

    protected $message = 'Bad request.';

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}