<?php

namespace Shantilab\YandexDirect\User;

use Shantilab\YandexDirect\Config;
use Shantilab\YandexDirect\Sender;

class Auth implements \ArrayAccess
{
    private $config;
    private $authorizeLink;

    public function __construct($params = [])
    {
        $this->setDefaultParams();

        if ($params)
            $this->setParams($params);

        $this->init();
    }

    private function setDefaultParams()
    {
        $conf = new Config();
        $this->config = $conf->getConfig();
    }

    public function setParams($params = [])
    {
        $this->config = $params + $this->config;
        $this->init();

        return $this;
    }

    private function init()
    {
        $this->setAuthorizeUrl();
    }

    private function setAuthorizeUrl()
    {
        $params = [
            'response_type' => $this->config['responseType'],
            'client_id' => $this->config['applicationID'],
        ];

        if ($this->config['url']['redirect'])
            $params['redirect_uri'] = $this->config['url']['redirect'];

        $this->authorizeLink = $this->config['url']['auth'] . '?' . http_build_query($params);
    }

    public function getAuthorizeUrl($state = '')
    {
        return $state ? $this->authorizeLink . '&state=' . $state : $this->authorizeLink;
    }

    public static function getInfo($token)
    {
        if ($token){
            $logInfoSender = new Sender('https://login.yandex.ru/info');
            $loginInfoResponse = $logInfoSender->exec([
                'format' => 'json',
                'oauth_token' => $token
            ]);

            return $loginInfoResponse->getFields();
        }
    }

    public function getToken()
    {
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $this->config['code'],
            'client_id' => $this->config['applicationID'],
            'client_secret' => $this->config['applicationPassword']
        ];

        $resultResponse = (new Sender($this->config['url']['token']))->exec($data);

        $result = $resultResponse->getFields();

        if (empty($result['error'])) {
            if ($result['access_token']){
                return $result;
            }
        } else {
            // выброс ошибок исключений
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->config[] = $value;
        } else {
            $this->config[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}