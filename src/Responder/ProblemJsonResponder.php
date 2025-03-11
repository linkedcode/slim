<?php

namespace Linkedcode\Slim\Responder;

use Linkedcode\Slim\ApiProblem\ApiProblemException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ProblemJsonResponder
{
    private ResponseFactoryInterface $responseFactory;
    private ServerRequestInterface $request;
    private Throwable $exception;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ServerRequestInterface $request,
        Throwable $exception
    ) {
        $this->responseFactory = $responseFactory;
        $this->request = $request;
        $this->exception = $exception;
    }

    public function createResponse(): ResponseInterface
    {
        $payload = json_encode($this->getBody(), JSON_PRETTY_PRINT);

        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/problem+json')
            ->withStatus($this->getStatusCode());
        
        $response->getBody()->write($payload);

        return $response;
    }

    private function getBody(): array
    {
        if ($this->exception instanceof ApiProblemException) {
            $body = $this->exception->getProblem()->getBody();
        } else {
            $body = [
                'type' => $this->getType(),
                'status' => $this->getStatusCode(),
                'detail' => $this->getDetail(),
                'instance' => $this->getInstance()
            ];
        }

        return $body;
    }

    private function getStatusCode(): int
    {
        return (int) $this->exception->getCode();
    }

    private function getDetail(): string
    {
        return $this->exception->getMessage();
    }

    private function getType(): string
    {
        return 'about:blank';
    }

    private function getInstance(): string
    {
        return $this->request->getUri()->getPath();
    }
}