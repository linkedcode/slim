<?php

namespace Linkedcode\Slim\Middleware;

use Linkedcode\Slim\Exception\ValidationException;
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
            $invalidParams = [];

            foreach ($e->getErrors() as $key => $messages) {
                foreach ($messages as $message) {
                    $invalidParams[] = array(
                        'name' => $key,
                        'reason' => $message
                    );
                }
            }

            $response = new Response();
            $response->getBody()->write(json_encode(array(
                'title' => $e->getMessage(),
                'status' => intval($e->getCode()),
                "invalid-params" => $invalidParams
            )));

            return $response
                ->withStatus($e->getCode())
                ->withHeader('Content-Type', 'application/vnd.api+json');
        }
    }
}
