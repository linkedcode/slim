<?php

namespace Linkedcode\Slim\Handler;

use Psr\Http\Message\ResponseInterface;
use Linkedcode\Slim\Responder\ProblemJsonResponder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

class ApiProblemHandler implements ErrorHandlerInterface
{
    private ServerRequestInterface $request;

    private Throwable $exception;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $this->request = $request;
        $this->exception = $exception;

        return $this->respond();
    }

    protected function respond(): ResponseInterface
    {
        $problemJson = new ProblemJsonResponder(
            $this->responseFactory,
            $this->request,
            $this->exception
        );

        return $problemJson->createResponse();
    }
}
