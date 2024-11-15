<?php

namespace Linkedcode\Slim\Request;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRequest
{
    protected function __construct() {}

    public static function fromRequest(ServerRequestInterface $request): static
    {
        return static::fromArray($request->getParsedBody());
    }

    public static function fromArray(array $data): static
    {
        $request = new static;

        foreach ($data as $key => $value) {
            if (property_exists($request, $key)) {
                $request->$key = $value;
            }
        }

        return $request;
    }
}