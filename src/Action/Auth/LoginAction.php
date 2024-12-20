<?php

namespace Linkedcode\Slim\Action\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Linkedcode\Slim\Settings;

class LoginAction 
{
    use CommonTrait;

    const ACCESS_TOKEN_URL = "auth/token";

    public function __construct(Settings $settings)
    {
        $this->userRepository = $userRepository;
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

        $headers = array(
            'Authorization: Bearer ' . $token
        );

        $res = $this->post($this->getAuthUrl(self::ACCESS_TOKEN_URL), $body, $headers);

        if ($this->curlInfo['http_code'] == "200") {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json')->withStatus($this->curlInfo['http_code']);
        }
    }
}
