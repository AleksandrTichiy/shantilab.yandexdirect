<?php

namespace Shantilab\YandexDirect\Account;

use Bitrix\Main\Type\DateTime;
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

        $res = (array) $this->getFromBase($params);
        $res = current($res);

        if ($res['ID']){
            $fields = $this->get();

            if (!isset($fields['TOKEN_FINAL_DATE']))
                $fields['TOKEN_FINAL_DATE'] = '';

            AccountsTable::update($res['ID'], $fields);
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

    private function getFromBase($getListParams){
        $result = AccountsTable::getList($getListParams);

        return $result->fetchAll();
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

    public function getToken()
    {
        return $this->fields['ACCESS_TOKEN'];
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
        $result = current($result);

        $this->set($result);
    }
}