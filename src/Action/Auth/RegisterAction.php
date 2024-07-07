<?php

namespace Linkedcode\Slim\Action\Auth;

use Linkedcode\Slim\Settings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisterAction
{
    use CommonTrait;

    const REGISTER_URL = "jwtregi";

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $token = $this->getClientToken();

        $body = $request->getParsedBody();
        $body['username'] = $body['email'];
        $body['grant_type'] = 'password';
        $body['client_id'] = '1';
        $body['client_secret'] = 'secret';
        $body['scope'] = 'all';

        $headers = array(
            'Authorization: Bearer ' . $token
        );

        $res = $this->post($this->getAuthUrl(self::REGISTER_URL), $body, $headers);

        if ($this->curlInfo['http_code'] == "200") {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
