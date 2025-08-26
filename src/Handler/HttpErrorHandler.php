<?php

namespace Linkedcode\Slim\Handler;

use Linkedcode\Base\Responder\ProblemJsonResponder;
use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\ErrorHandler;

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
