<?php

namespace Shantilab\YandexDirect\Account;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\DB;
use Shantilab\YandexDirect\AccountsTable;
use Shantilab\YandexDirect\Api;

class Account implements AccountInterface, \ArrayAccess
{
    private $fields;

    public function __construct($fields)
    {
        $this->set($fields);
        return $this;
    }

    public function save()
    {
        $params = [
            'select' => ['ID'],
            'filter' => [
                'USER_ID' => $this->getUserId(),
                'LOGIN' => $this->getLogin(),
            ]
        ];

        $res = $this->getFromBase($params);

        if ($res['ID']){
            AccountsTable::update($res['ID'], $this->get());
        }
        else{
            AccountsTable::add($this->get());
        }

        return $this;
    }

    public function delete()
    {
        if (AccountsTable::delete($this->fields['ID']))
            unset($this->fields);

        return $this;
    }

    public function __call($method, $args)
    {
        $yandexApi = new Api($this);

        $params = empty($args) ? [] : $args[0];

        $return = $yandexApi->apiQuery(ucfirst($method), $params);

        unset($yandexApi);

        return $return;
    }

    public static function getCustom($getListParams)
    {
        $result = AccountsTable::getList($getListParams);

        return $result->fetchAll();
    }

    public function getBy($filter, $actualToken = true)
    {
        if (!$filter['USER_ID']){
            global $USER;
            $filter['USER_ID'] = $USER->GetId();
        }

        if ($actualToken)
            $filter['>TOKEN_FINAL_DATE'] = new DateTime();

        $getList = ['filter' => $filter];

        $result = $this->getFromBase($getList);

        $this->set($result);
    }

    private function getFromBase($getListParams){
        return AccountsTable::getRow($getListParams);
    }

    public function set($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function get()
    {
        return $this->fields;
    }

    public function getToken($checkActual = false)
    {
        if ($this->fields['TOKEN_FINAL_DATE'] > new DateTime())
            return $this->fields['ACCESS_TOKEN'];

        return null;
    }

    public function getMasterToken()
    {
        return $this->fields['MASTER_TOKEN'];
    }

    public function getFinanceToken($method)
    {
        $login = toLower(str_replace('.', '-', $this->getLogin()));
        $masterToken = $this->getMasterToken();
        $financeNum = intval($this->getFinancialNum() + 1);

        $financeToken = hash("sha256", $masterToken . $financeNum . $method . $login);

        return $financeToken;
    }

    public function incrementFinanceOperation(){
        AccountsTable::update($this->fields['ID'], [
            'FINANCE_NUM' => new DB\SqlExpression('?# + 1', 'FINANCE_NUM')
        ]);
    }

    public function resetFinanceOperationsNum(){
        AccountsTable::update($this->fields['ID'], [
            'FINANCE_NUM' => 0
        ]);
    }

    public function getFinancelNum()
    {
        return intval($this->fields['FINANCE_NUM']);
    }

    public function getUserId()
    {
        return $this->fields['USER_ID'];
    }

    public function getLogin()
    {
        return $this->fields['LOGIN'];
    }

    public function offsetExists($offset)
    {
        return isset($this->fields[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->fields[$offset]) ? $this->fields[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->fields[] = $value;
        } else {
            $this->fields[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->fields[$offset]);
    }
}