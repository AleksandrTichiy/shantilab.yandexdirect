<?php

namespace Shantilab\YandexDirect\Account;

interface AccountCollectionInterface
{
    public function get($onlyActual = true);
}