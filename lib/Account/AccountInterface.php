<?php

namespace Shantilab\YandexDirect\Account;

interface AccountInterface
{
    public function __construct($fields);
    public function getToken();
    public function getUserId();
    public function getLogin();
}