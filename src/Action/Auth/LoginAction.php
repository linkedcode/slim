<?php

namespace Linkedcode\Slim\Action\Auth;

use App\Infrastructure\Persistence\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
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
        $body['grant_type'] = 'password';
        $body['client_id'] = '1';
        $body['client_secret'] = 'secret';

        $headers = array(
            'Authorization: Bearer ' . $token
        );

        $res = $this->post($this->getAuthUrl(self::ACCESS_TOKEN_URL), $body, $headers);

        if ($this->curlInfo['http_code'] == "200") {
            $this->createUser($res);
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write($res);
            return $response->withHeader('Content-Type', 'application/json')->withStatus($this->curlInfo['http_code']);
        }
    }

    /**
     * @return Token
     */
    protected function parseToken($jwt) : Token
    {
        $key = $this->settings->getPublicKey();

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file($key),
            InMemory::base64Encoded('mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=')
        );

        $arr = json_decode($jwt);
        $token = $config->parser()->parse($arr->access_token);
        return $token;
    }

    public function createUser($accessToken)
    {
        try {
            $token = $this->parseToken($accessToken);
            $claims = $token->claims();
            $id = (int) $claims->get('sub');
            $user = $this->userRepository->find($id);
            if (!$user) {
                $this->userRepository->createUserFromId($id);
                return true;
            }
        } catch (Exception $e) {
            $this->createUser($accessToken);
        }
    }
}
