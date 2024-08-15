<?php

namespace Linkedcode\Slim\Responder;

use Doctrine\ORM\PersistentCollection;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractResponder
{
    protected $transformer;
    
    public function withJson(ResponseInterface $response, $data = null, int $options = 0): ResponseInterface
    {
        if (is_bool($data)) {
            $data = array('success' => $data);
        } else {
            $data = $this->transform($data);
        }

        $response = $response->withHeader('Content-Type', 'application/json');
        $encoded = (string) json_encode($data, $options);
        $response->getBody()->write($encoded);
        return $response;
    }

    protected function transform(mixed $data)
    {
        if (is_array($data) || $data instanceof PersistentCollection) {
            $transformed = [];
            foreach ($data as $item) {
                $transformed[] = $this->transformer->transform($item);
            }
        } else {
            $transformed = $this->transformer->transform($data);
        }

        return $transformed;
    }
}