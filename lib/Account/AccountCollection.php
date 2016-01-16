<?php

namespace Shantilab\YandexDirect\Account;

use Bitrix\Main\Type\DateTime;
use Shantilab\YandexDirect\AccountsTable;

class AccountCollection implements AccountCollectionInterface, \IteratorAggregate
{
    private $userId;
    private $accounts;

    public function __construct($userId = null, $onlyActual = true)
    {
        $userId = intval($userId);

        if (!$userId){
            global $USER;
            $userId = $USER->getId();
        }

        $this->userId = $userId;

        $this->get($onlyActual);
    }

    public static function getCustom($getListParams)
    {
        $result = AccountsTable::getList($getListParams);

        return $result->fetchAll();
    }

    public function get($onlyActual = true)
    {
        $getListParams['filter']['USER_ID'] = $this->userId;

        if ($onlyActual)
            $getListParams['filter']['>=TOKEN_FINAL_DATE'] = new DateTime();

        $accounts = $this->getFromBase($getListParams);

        if ($accounts)
            unset($this->accounts);

        foreach($accounts as $account){
            $this->accounts[] = new Account($account);
        }

        return $this->accounts;
    }

    public function getFromBase($getListParams){
        $result = AccountsTable::getList($getListParams);

        return $result->fetchAll();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->accounts);
    }
}