<?php

namespace Linkedcode\Slim\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\ErrorHandler;
use Linkedcode\Slim\Responder\ProblemJsonResponder;

class HttpErrorHandler extends ErrorHandler
{
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
