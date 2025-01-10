<?php

namespace Linkedcode\Slim\Action\Auth;

use App\Infrastructure\Persistence\UserRepository;
use Linkedcode\Slim\Settings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use Lcobucci\JWT\Token;

class RegisterVerifyAction
{
    use CommonTrait;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository, Settings $settings)
    {
        $this->userRepository = $userRepository;
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

            $token = $this->parseToken($res);
            if ($token instanceof Token) {
                $this->createUser($res);
            }
    
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json')->withStatus($this->curlInfo['http_code']);
        }
    }
}
