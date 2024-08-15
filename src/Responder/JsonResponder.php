<?php

namespace Linkedcode\Slim\Responder;

use Psr\Http\Message\ResponseInterface;

class JsonResponder
{
    public function withJson(ResponseInterface $response, $data = null, int $options = 0): ResponseInterface
    {
        $response = $response->withHeader('Content-Type', 'application/json');
        $encoded = (string) json_encode($data, $options);
        $response->getBody()->write($encoded);
        return $response;
    }
}