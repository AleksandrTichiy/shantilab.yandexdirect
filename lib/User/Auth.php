<?php

namespace Shantilab\YandexDirect\User;

use Shantilab\YandexDirect\Config;
use Shantilab\YandexDirect\Sender;
use Shantilab\YandexDirect\Exceptions\SettingParameterNullException;

/**
 * Class Auth
 * @package Shantilab\YandexDirect\User
 */
class Auth implements \ArrayAccess
{
    /**
     * @var
     */
    private $config;
    /**
     * @var
     */
    private $authorizeLink;

    /**
     * Auth constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->setDefaultParams();

        if ($params)
            $this->setParams($params);

        $this->init();
    }

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\IO\FileNotFoundException
     * @throws \Bitrix\Main\IO\InvalidPathException
     */
    private function setDefaultParams()
    {
        $conf = new Config();
        $this->config = $conf->getConfig();
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams($params = [])
    {
        $this->config = $params + $this->config;
        $this->init();

        return $this;
    }

    /**
     * @throws SettingParameterNullException
     */
    private function init()
    {
        $this->setAuthorizeUrl();
    }

    /**
     * @throws SettingParameterNullException
     */
    private function setAuthorizeUrl()
    {
        if (!$this->config['responseType'])
            throw new SettingParameterNullException('responseType');
        if (!$this->config['applicationID'])
            throw new SettingParameterNullException('applicationID');

        $params = [
            'response_type' => $this->config['responseType'],
            'client_id' => $this->config['applicationID'],
        ];

        if ($this->config['url']['redirect'])
            $params['redirect_uri'] = $this->config['url']['redirect'];

        $this->authorizeLink = $this->config['url']['auth'] . '?' . http_build_query($params);
    }

    /**
     * @param string $state
     * @return string
     */
    public function getAuthorizeUrl($state = '')
    {
        return $state ? $this->authorizeLink . '&state=' . $state : $this->authorizeLink;
    }

    /**
     * @param $token
     * @return \Shantilab\YandexDirect\Response
     * @throws \Shantilab\YandexDirect\Exceptions\InvalidSendExcpetion
     */
    public static function getInfo($token)
    {
        if ($token){
            $logInfoSender = new Sender('https://login.yandex.ru/info');
            $loginInfoResponse = $logInfoSender->exec([
                'format' => 'json',
                'oauth_token' => $token
            ]);

            return $loginInfoResponse;
        }
    }

    /**
     * @return \Shantilab\YandexDirect\Response
     * @throws SettingParameterNullException
     * @throws \Shantilab\YandexDirect\Exceptions\InvalidSendExcpetion
     */
    public function getToken()
    {
        if (!$this->config['applicationID'])
            throw new SettingParameterNullException('applicationID');
        if (!$this->config['applicationPassword'])
            throw new SettingParameterNullException('applicationPassword');
        if (!$this->config['code'])
            throw new SettingParameterNullException('code');

        $data = [
            'grant_type' => 'authorization_code',
            'code' => $this->config['code'],
            'client_id' => $this->config['applicationID'],
            'client_secret' => $this->config['applicationPassword']
        ];

        $resultResponse = (new Sender($this->config['url']['token']))->exec($data);

        return $resultResponse;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    /**
     * @param mixed $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->config[] = $value;
        } else {
            $this->config[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}