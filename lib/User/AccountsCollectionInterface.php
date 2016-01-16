<?php

namespace Shantilab\YandexDirect\User;


interface AccountsCollectionInterface
{
    public function getAccounts();
    public function saveTokenInfo($tokenInfo);
    public function getTokenInfo($login = null);
}