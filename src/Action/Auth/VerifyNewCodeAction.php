<?php

namespace Linkedcode\Slim\Action\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VerifyNewCodeAction
{
    use CommonTrait;
    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $ch = curl_init();

        $body = $request->getParsedBody();
        $body['username'] = $body['email'];

        $payload = json_encode($body);

        $url = $this->getAuthUrl("auth/verify/code");

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] == "200") {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
