<?php

namespace Linkedcode\Slim\Action\Auth;

use App\Infrastructure\Persistence\UserRepository;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Linkedcode\Slim\Settings;

class LoginAction 
{
    use CommonTrait;

    const ACCESS_TOKEN_URL = "auth/token";

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository, Settings $settings)
    {
        $this->userRepository = $userRepository;
        $this->settings = $settings;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $token = $this->getClientToken();

        $body = $request->getParsedBody();
        $body['username'] = $body['email'];

        $body = array_merge($body, $this->settings->get('oauth.app'));

        $headers = array(
            'Authorization: Bearer ' . $token
        );

        $res = $this->post($this->getAuthUrl(self::ACCESS_TOKEN_URL), $body, $headers);

        if ($this->curlInfo['http_code'] == "200") {
            $token = $this->parseToken($res);
            if ($token instanceof Token) {
                $this->createUser($token);
            }
    
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json')->withStatus($this->curlInfo['http_code']);
        }
    }
}
