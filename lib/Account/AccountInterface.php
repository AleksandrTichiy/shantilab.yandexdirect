<?php

namespace Shantilab\YandexDirect\Account;

/**
 * Interface AccountInterface
 * @package Shantilab\YandexDirect\Account
 */
interface AccountInterface
{
    /**
     * AccountInterface constructor.
     * @param $fields
     */
    public function __construct($fields);

    /**
     * @return mixed
     */
    public function getToken();

    /**
     * @return mixed
     */
    public function getUserId();

    /**
     * @return mixed
     */
    public function getLogin();

    /**
     * @return mixed
     */
    public function getFinancialToken($method);

    /**
     * @return mixed
     */
    public function getFinancialNum();
}