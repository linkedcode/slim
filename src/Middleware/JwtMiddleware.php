<?php

namespace Linkedcode\Slim\Middleware;

use Exception;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class JwtMiddleware implements MiddlewareInterface
{
    protected $fake = false;

    private string $configDir;

    public function __construct($configDir)
    {
        $this->configDir = $configDir;
    }

    public function setFake($bool)
    {
        $this->fake = $bool;
    }

    public function process(ServerRequestInterface $request, RequestHandler $handler): ResponseInterface
    {
        if ($this->fake) {
            $request = $request->withAttribute('uid', 100);
            return $handler->handle($request);
        }

        $bearer = $request->getHeader("Authorization")[0];
        $accessToken = substr($bearer, strlen("Bearer "));

        if (empty($accessToken)) {
            throw new HttpUnauthorizedException($request);
        }
        
        $file = $this->configDir . '/config/public.key';
        /*$config = Configuration::forAsymmetricSigner(
            new Signer\Rsa\Sha256(),
            InMemory::plainText(''),
            LocalFileReference::file($file),
        );*/
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file($file),
            InMemory::base64Encoded('mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=')
        );
        
        try {
            $token = $config->parser()->parse($accessToken);
        } catch (InvalidTokenStructure $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        } catch (Exception $e) {
            throw $e;
        }

        $uid = $token->claims()->get('sub');
        $request = $request->withAttribute('uid', $uid);
        $request = $request->withAttribute('user_id', $uid);

        return $handler->handle($request);
    }
}
