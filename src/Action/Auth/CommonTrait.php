<?php

namespace Linkedcode\Slim\Action\Auth;

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

        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => '2',
            'client_secret' => 'secret',
            'scope' => 'all'
        ];

        $json = $this->post($url, $body);
        $arr = json_decode($json);

        return $arr->access_token;
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
}