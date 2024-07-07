<?php

namespace Linkedcode\Slim\Action\Auth;

use Linkedcode\Slim\Settings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResetVerifyAction
{
    use CommonTrait;

    private const VERIFY_URL = "auth/verify/code";

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $token = $this->getClientToken();
        $server = $request->getServerParams();

        $body = $request->getParsedBody();
        $body['username'] = $body['email'];
        $body['ip'] = $server['REMOTE_ADDR'];

        $headers = array(
            'Authorization: Bearer ' . $token
        );

        $res = $this->post($this->getAuthUrl(self::VERIFY_URL), $body, $headers);

        if ($this->curlInfo['http_code'] == "200") {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write((string) $res);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
