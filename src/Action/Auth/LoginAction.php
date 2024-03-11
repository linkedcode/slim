<?php

namespace Linkedcode\Slim\Action\Auth;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Linkedcode\Slim\Service\Settings;

class LoginAction 
{
    use CommonTrait;

    const ACCESS_TOKEN_URL = "auth/token";

    private $pdo;

    public function __construct(PDO $pdo, Settings $settings)
    {
        $this->pdo = $pdo;
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
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText(''),
            LocalFileReference::file(BASE_DIR . '/config/public.key'),
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
            $lenght = 16;
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));

            $sql = "SELECT * FROM user WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(
                "id" => $claims->get('sub')
            ));
            
            if ($stmt->fetch()) {
                return true;
            }

            $sql = "INSERT INTO user (id, name) VALUES (:id, :name)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(
                "id" => $claims->get('sub'),
                'name' => substr(bin2hex($bytes), 0, $lenght)
            ));
            return true;
        } catch (Exception $e) {
            $this->createUser($accessToken);
        }
    }
}
