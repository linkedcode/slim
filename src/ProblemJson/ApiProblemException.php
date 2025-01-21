<?php

namespace Linkedcode\Slim\ProblemJson;

use Exception;
use Throwable;

class ApiProblemException extends Exception
{
    private ApiProblem $apiProblem;

    public function __construct(ApiProblem $apiProblem, Throwable|null $previous = null)
    {
        $this->apiProblem = $apiProblem;
        
        parent::__construct(
            $apiProblem->getTitle(),
            $apiProblem->getStatusCode(),
            $previous
        );
    }

    public function getApiProblem(): ApiProblem
    {
        return $this->apiProblem;
    }
}