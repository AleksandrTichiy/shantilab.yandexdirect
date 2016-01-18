<?php

namespace Shantilab\YandexDirect;

use \Bitrix\Main\Web\Json;
use Shantilab\YandexDirect\Account\AccountInterface;

class Api
{
    private $config;
    private $sender;
    private $apiUrl;
    private $account;

    public function __construct(AccountInterface $account)
    {
        $this->account = $account;

        $this->setDefaultParams();
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
            'token' => $this->account->getToken()
        ];

        if ($this->isFinancialOperation($method)){
            $params['finance_token']  = $this->account->getFinanceToken($method);
            $params['operation_num']  = $this->account->getFinanceNum() + 1;
        }else{
            $params['application_id'] = $this->config['applicationID'];
            $params['token'] = $this->account->getToken();
        }

        $params =  Json::encode($params);
        $resultResponse = $this->sender->exec($params);
        $result = $resultResponse->getFields();

        if (!empty($result)) {
            if (isset($result['error_code']) && isset($result['error_str'])){}
                //обработка ошибок
            if (!empty($result['error_detail'])){}
                //обработка ошибок
        }

        if ($this->isFinancialOperation($method) && !$result['error_code']){
            $this->account->incrementFinanceOperation();
        }

        return $result;
    }

    public function __call($method, $args)
    {
        $params = empty($args) ? [] : $args[0];
        return $this->apiQuery(ucfirst($method), $params);
    }

    private function isFinancialOperation($operation)
    {
        if (in_array($operation, $this->config['financialOperations']))
            return true;

        return false;
    }
}