<?php

namespace Linkedcode\Slim\Test;

use Linkedcode\Slim\Application;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

abstract class AbstractTestCase extends TestCase
{
    protected static Application|null $app = null;

    public static function setUpBeforeClass(): void
    {
        if (static::$app === null) {
            static::$app = self::initApp();
            static::configureApp();
        }
    }

    protected static function configureApp(): void {}

    private static function initApp(): Application
    {
        $file = '/app/definitions.php';
        $dir = __DIR__;
        $found = false;

        do {
            if (file_exists($dir . $file)) {
                $found = true;
            } else {
                $dir = dirname($dir);
            }
        } while ($found === false);

        return new Application($dir);
    }

    protected function postRequest(string $uri, array $postData, array $serverParams = []): ServerRequestInterface
    {
        $serverParams['REMOTE_ADDR'] = '127.0.0.1';

        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('POST', $uri, $serverParams);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode($postData));

        $request = $this->addToken($request);

        return $request;
    }

    protected function addToken($request): ServerRequestInterface
    {
        $token = $this->getToken();
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
        return $request;
    }

    protected function getToken()
    {
        $data = [
            'email' => 'info@linkedcode.com',
            'password' => 'Q;s*c;cKE}8E9f:'
        ];

        $request = $this->postRequest('/login', $data);
        $response = self::$app->handle($request);
        $data = json_decode($response->getBody());
        return $data->access_token;
    }
}
