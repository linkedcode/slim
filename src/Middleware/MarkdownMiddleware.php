<?php

namespace Linkedcode\Slim\Middleware;

use League\CommonMark\CommonMarkConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MarkdownMiddleware implements MiddlewareInterface
{
    protected $converter;

    public function __construct(CommonMarkConverter $converter)
    {
        $this->converter = $converter;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = $request->getParsedBody();
        $body['content'] = $this->converter->convertToHtml($body['markdown'])->getContent();
        $request = $request->withParsedBody($body);

        return $handler->handle($request);
    }
}