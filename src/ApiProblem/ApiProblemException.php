<?php

namespace Linkedcode\Slim\ApiProblem;

use Exception;
use Throwable;

class ApiProblemException extends Exception
{
    private ApiProblem $problem;

    public function __construct(ApiProblem $problem, Throwable|null $previous = null)
    {
        $this->problem = $problem;
        
        parent::__construct(
            $problem->getDetail(), $problem->getStatusCode(), $previous
        );
    }

    public function getProblem(): ApiProblem
    {
        return $this->problem;
    }
}