<?php

namespace Linkedcode\Slim\Middleware;

use Linkedcode\Slim\Exception\ValidationException;
use Linkedcode\Slim\ProblemJson\ApiProblemException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class ValidationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $response = new Response();
            $response->getBody()->write(json_encode(array(
                'title' => $e->getMessage(),
                'status' => intval($e->getCode()),
                "invalid-params" => $e->getErrors()
            )));

            return $response
                ->withStatus($e->getCode())
                ->withHeader('Content-Type', 'application/vnd.api+json');
        } catch (ApiProblemException $e) {
            $e->getApiProblem()->setInstance($request->getUri()->getPath());

            $response = new Response();
            $response->getBody()->write($e->getApiProblem()->getBody());

            return $response
                ->withStatus($e->getCode())
                ->withHeader('Content-Type', 'application/problem+json');
        }
    }
}
