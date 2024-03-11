<?php

namespace Linkedcode\Slim\Action\Auth;

use Linkedcode\Slim\Service\Settings;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VerifyAction
{
    use CommonTrait;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $token = $this->getClientToken();

        $headers = array(
            'Authorization: Bearer ' . $token
        );

        $body = $request->getParsedBody();
        
        $url = $this->getAuthUrl("verify");

        $res = $this->post($url, $body, $headers);

        if ($this->curlInfo['http_code'] == "200") {
            $body['username'] = $body['email'];
            $body['grant_type'] = 'password';
            $body['client_id'] = '1';
            $body['client_secret'] = 'secret';
    
            $res = $this->post($this->getAuthUrl("auth/token"), $body, $headers);
    
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json')->withStatus($this->curlInfo['http_code']);
        }
    }
}
