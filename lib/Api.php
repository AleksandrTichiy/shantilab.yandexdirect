<?php

namespace Shantilab\YandexDirect;

use \Bitrix\Main\Web\Json;
use Shantilab\YandexDirect\Account\AccountInterface;
use Shantilab\YandexDirect\Exceptions\SettingParameterNullException;
use Bitrix\Main\ArgumentNullException;

/**
 * Class Api
 * @package Shantilab\YandexDirect
 */
class Api
{
    /**
     * @var
     */
    private $config;
    /**
     * @var
     */
    private $sender;
    /**
     * @var
     */
    private $apiUrl;
    /**
     * @var AccountInterface
     */
    private $account;

    /**
     * Api constructor.
     * @param AccountInterface $account
     */
    public function __construct(AccountInterface $account)
    {
        $this->account = $account;

        $this->setDefaultParams();
        $this->init();
    }

    /**
     * @throws ArgumentNullException
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
     *
     */
    private function init()
    {
        $this->apiUrl = ($this->config['testMode']) ? $this->config['url']['testApi'] : $this->config['url']['api'];

        $this->sender = new Sender($this->apiUrl);
    }

    /**
     * @param $method
     * @param array $params
     * @return mixed
     * @throws ArgumentNullException
     * @throws SettingParameterNullException
     */
    public function apiQuery($method, $params = array())
    {
        $params = [
            'method' => $method,
            'param' => $params,
            'locale' => ($this->config['locale']) ? $this->config['locale'] : 'ru',
        ];

        if ($this->isFinancialOperation($method)){
            $params['finance_token']  = $this->account->getFinanceToken($method);
            $params['operation_num']  = $this->account->getFinanceNum() + 1;

            if ($params['finance_token'])
                throw new ArgumentNullException('finance_token');
            if ($params['operation_num'])
                throw new ArgumentNullException('operation_num');
        }else{
            $params['application_id'] = $this->config['applicationID'];
            $params['token'] = $this->account->getToken();

            if (!$this->config['applicationID'])
                throw new SettingParameterNullException('applicationID');
            if (!$params['token'])
                throw new ArgumentNullException('token');
        }

        $params =  Json::encode($params);
        $resultResponse = $this->sender->exec($params);

        if ($this->isFinancialOperation($method) && !$resultResponse->getErrors()){
            $this->account->incrementFinanceOperation();
        }

        return $resultResponse;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws ArgumentNullException
     * @throws SettingParameterNullException
     */
    public function __call($method, $args)
    {
        $params = empty($args) ? [] : $args[0];
        return $this->apiQuery(ucfirst($method), $params);
    }

    /**
     * @param $operation
     * @return bool
     */
    private function isFinancialOperation($operation)
    {
        if (in_array($operation, $this->config['financialOperations']))
            return true;

        return false;
    }
}