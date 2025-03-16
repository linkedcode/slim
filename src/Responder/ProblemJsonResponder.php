<?php

namespace Linkedcode\Slim\Responder;

use Linkedcode\Slim\ApiProblem\ApiProblem;
use Linkedcode\Slim\ApiProblem\ApiProblemException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProblemJsonResponder
{
    private ResponseFactoryInterface $responseFactory;
    private ApiProblem $problem;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ServerRequestInterface $request,
        ApiProblemException $exception
    ) {
        $this->responseFactory = $responseFactory;
        $this->problem = $exception->getProblem();
        $this->problem->setInstance($request->getUri()->getPath());
    }

    public function createResponse(): ResponseInterface
    {
        $payload = json_encode($this->problem->getBody(), JSON_PRETTY_PRINT);

        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/problem+json')
            ->withStatus($this->problem->getStatusCode());
        
        $response->getBody()->write($payload);

        return $response;
    }
}