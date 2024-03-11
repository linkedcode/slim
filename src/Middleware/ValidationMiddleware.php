<?php

namespace Linkedcode\Validation;

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
            foreach ($e->getErrors() as $key => $messages) {
                foreach ($messages as $message) {
                    $error = [
                        'source' => array("pointer" => $key),
                        'title' => $message,
                        'detail' => $message,
                    ];

                    $errors[] = $error;
                }
            }

            $response = new Response();
            $response->getBody()->write(json_encode(array(
                "errors" => $errors
            )));

            return $response
                ->withStatus($e->getCode())
                ->withHeader('Content-Type', 'application/vnd.api+json');
        }
    }
}
