<?php

namespace Shantilab\YandexDirect;

use \Bitrix\Main\Web\Json;

class Api
{
    private $config;
    private $sender;
    private $apiUrl;

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
        $this->apiUrl = ($this->config['testMode']) ? $this->config['url']['testApi'] : $this->config['url']['api'];

        $this->sender = new Sender($this->apiUrl);
    }

    public function apiQuery($method, $params = array())
    {
        $params = [
            'method' => $method,
            'param' => $params,
            'locale' => ($this->config['locale']) ? $this->config['locale'] : 'ru',
            'application_id' => $this->config['applicationID'],
            'token' => $this->config['token']
        ];

        $params =  Json::encode($params);
        $resultResponse = $this->sender->exec($params);
        $result = $resultResponse->getFields();

        if (!empty($result)) {
            if (isset($result['error_code']) && isset($result['error_str'])){}
                //обработка ошибок
            if (!empty($result['error_detail'])){}
                //обработка ошибок
        }

        return $result;
    }

    public function __call($method, $args)
    {
        $params = empty($args) ? [] : $args[0];
        return $this->apiQuery(ucfirst($method), $params);
    }

}