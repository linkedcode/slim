<?php

declare(strict_types=1);

namespace Linkedcode\Slim\Action;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

abstract class Action
{
    protected ServerRequestInterface $request;

    protected ResponseInterface $response;

    protected array $args;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (Exception $e) {
            throw $e;
        }
    }

    abstract protected function action(): ResponseInterface;

    protected function getFormData()
    {
        return $this->request->getParsedBody();
    }

    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException(
                $this->request,
                "Could not resolve argument `{$name}`."
            );
        }

        return $this->args[$name];
    }

    protected function respondWithData($data = null, int $statusCode = 200): ResponseInterface
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): ResponseInterface
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }
}
