<?php

namespace Linkedcode\Slim\Action\Auth;

use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Linkedcode\Slim\ProblemJson\ApiProblem;
use Linkedcode\Slim\ProblemJson\ApiProblemException;
use Linkedcode\Slim\Settings;

trait CommonTrait
{
    protected $curlInfo;

    protected Settings $settings;

    protected function getAuthUrl($path)
    {
        $url = $this->settings->get('authUrl') . $path;
        return $url;
    }

    protected function getClientToken()
    {
        $url = $this->getAuthUrl("token");

        $body = $this->settings->get('oauth.auth');

        $json = $this->post($url, $body);

        if ($this->curlInfo['http_code'] == 200) {
            $arr = json_decode($json);
            return $arr->access_token;
        } else {
            $apiProblem = ApiProblem::fromApiProblem($json);
            throw new ApiProblemException($apiProblem);
        }
    }

    protected function post(string $url, array $body, array $headers = array())
    {
        $ch = curl_init();
        
        $headers = array_merge(array(
            'Content-Type: application/json'
        ), $headers);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $this->localhost($ch, $url);
        
        $res = curl_exec($ch);
        $this->curlInfo = curl_getinfo($ch);
        curl_close($ch);

        return $res;
    }

    protected function localhost($ch, $url)
    {
        if (stripos($url, '.local')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
    }

    private function parseToken(string $jwt): Token
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

    private function createUser(Token $token): bool
    {
        try {
            $claims = $token->claims();
            $id = (int) $claims->get('sub');
            $user = $this->userRepository->find($id);
            if (!$user) {
                $this->userRepository->createUserFromId($id);
            }
            return true;
        } catch (Exception $e) {
            $this->createUser($token);
        }
    }
}