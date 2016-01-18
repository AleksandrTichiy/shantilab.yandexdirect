<?php

namespace Shantilab\YandexDirect\Account;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ArgumentNullException;
use Shantilab\YandexDirect\AccountsTable;

/**
 * Class AccountCollection
 * @package Shantilab\YandexDirect\Account
 */
class AccountCollection implements AccountCollectionInterface, \IteratorAggregate
{
    /**
     * @var int
     */
    private $userId;
    /**
     * @var
     */
    private $accounts;

    /**
     * AccountCollection constructor.
     * @param null $userId
     * @param bool $onlyActual
     */
    public function __construct($userId = null, $onlyActual = true)
    {
        $userId = intval($userId);

        if (!$userId){
            global $USER;
            $userId = $USER->getId();
        }

        $this->userId = $userId;

        if (!$this->userId)
            throw new ArgumentNullException('USER_ID');

        $this->get($onlyActual);
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
     * @param bool $onlyActual
     * @return mixed
     */
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

    /**
     * @param $getListParams
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getFromBase($getListParams){
        $result = AccountsTable::getList($getListParams);

        return $result->fetchAll();
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->accounts);
    }
}