<?php

namespace Shantilab\YandexDirect\Account;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\DB;
use Bitrix\Main\ArgumentNullException;
use Shantilab\YandexDirect\AccountsTable;
use Shantilab\YandexDirect\Api;

/**
 * Class Account
 * @package Shantilab\YandexDirect\Account
 */
class Account implements AccountInterface, \ArrayAccess
{
    /**
     * @var
     */
    private $fields;

    /**
     * Account constructor.
     * @param $fields
     */
    public function __construct($fields)
    {
        $this->set($fields);
        return $this;
    }

    /**
     * @return $this
     * @throws ArgumentNullException
     */
    public function save()
    {
        $params = [
            'select' => ['ID'],
            'filter' => [
                'USER_ID' => $this->getUserId(),
                'LOGIN' => $this->getLogin(),
            ]
        ];

        if (!$params['filter']['USER_ID'])
            throw new ArgumentNullException('USER_ID');
        if (!$params['filter']['LOGIN'])
            throw new ArgumentNullException('LOGIN');

        $res = $this->getFromBase($params);

        if ($res['ID']){
            AccountsTable::update($res['ID'], $this->get());
        }
        else{
            AccountsTable::add($this->get());
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ArgumentNullException
     */
    public function delete()
    {
        if (!$this->fields['ID'])
            throw new ArgumentNullException('ID');

        if (AccountsTable::delete($this->fields['ID']))
            unset($this->fields);

        return $this;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Shantilab\YandexDirect\Exceptions\SettingParameterNullException
     */
    public function __call($method, $args)
    {
        $yandexApi = new Api($this);

        $params = empty($args) ? [] : $args[0];

        $return = $yandexApi->apiQuery(ucfirst($method), $params);

        unset($yandexApi);

        return $return;
    }

    /**
     * @param $getListParams
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getCustom($getListParams)
    {
        $result = AccountsTable::getList($getListParams);

        return $result->fetchAll();
    }

    /**
     * @param $filter
     * @param bool $actualToken
     * @throws ArgumentNullException
     */
    public function getBy($filter, $actualToken = true)
    {
        if (!$filter['USER_ID']){
            global $USER;
            $filter['USER_ID'] = $USER->GetId();
        }

        if (!$filter['USER_ID'])
            throw new ArgumentNullException('USER_ID');

        if ($actualToken)
            $filter['>TOKEN_FINAL_DATE'] = new DateTime();

        $getList = ['filter' => $filter];

        $result = $this->getFromBase($getList);

        $this->set($result);
    }

    /**
     * @param $getListParams
     * @return array|null
     */
    private function getFromBase($getListParams){
        return AccountsTable::getRow($getListParams);
    }

    /**
     * @param $fields
     * @return $this
     */
    public function set($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->fields;
    }

    /**
     * @param bool $checkActual
     * @return null
     */
    public function getToken($checkActual = false)
    {
        if ($this->fields['TOKEN_FINAL_DATE'] > new DateTime())
            return $this->fields['ACCESS_TOKEN'];

        return null;
    }

    /**
     * @return mixed
     */
    public function getMasterToken()
    {
        return $this->fields['MASTER_TOKEN'];
    }

    /**
     * @param $method
     * @return string
     */
    public function getFinanceToken($method)
    {
        $login = toLower(str_replace('.', '-', $this->getLogin()));
        $masterToken = $this->getMasterToken();
        $financeNum = intval($this->getFinancialNum() + 1);

        $financeToken = hash("sha256", $masterToken . $financeNum . $method . $login);

        return $financeToken;
    }

    /**
     * @throws ArgumentNullException
     */
    public function incrementFinanceOperation(){
        if (!$this->fields['ID'])
            throw new ArgumentNullException('ID');

        AccountsTable::update($this->fields['ID'], [
            'FINANCE_NUM' => new DB\SqlExpression('?# + 1', 'FINANCE_NUM')
        ]);
    }

    /**
     * @throws ArgumentNullException
     */
    public function resetFinanceOperationsNum(){
        if (!$this->fields['ID'])
            throw new ArgumentNullException('ID');

        AccountsTable::update($this->fields['ID'], [
            'FINANCE_NUM' => 0
        ]);
    }

    /**
     * @return int
     */
    public function getFinancelNum()
    {
        return intval($this->fields['FINANCE_NUM']);
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->fields['USER_ID'];
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->fields['LOGIN'];
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->fields[$offset]);
    }

    /**
     * @param mixed $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->fields[$offset]) ? $this->fields[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->fields[] = $value;
        } else {
            $this->fields[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->fields[$offset]);
    }
}