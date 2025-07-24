<?php

namespace Linkedcode\Slim\Request;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRequest
{
    use RequestValidatorTrait;

    private function __construct(array $data)
    {
        $data = $this->validate($data);

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function fromRequest(ServerRequestInterface $request): static
    {
        return new static($request->getParsedBody());
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }
}
