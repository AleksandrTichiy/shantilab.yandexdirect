<?php

namespace Shantilab\YandexDirect\Account;

/**
 * Interface AccountCollectionInterface
 * @package Shantilab\YandexDirect\Account
 */
interface AccountCollectionInterface
{
    /**
     * @param bool $onlyActual
     * @return mixed
     */
    public function get($onlyActual = true);
}