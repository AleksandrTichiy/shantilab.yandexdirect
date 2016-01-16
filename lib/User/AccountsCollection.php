<?php

namespace Shantilab\YandexDirect\User;

use Shantilab\YandexDirect\UserTable;

class AccountsCollection implements AccountsCollectionInterface
{
    private $userId;

    public function __construct($userId = null)
    {
        $userId = intval($userId);

        if (!$userId){
            global $USER;
            $userId = $USER->getId();
        }

        $this->userId = $userId;
    }

    public function getAccounts($onlyActual = false)
    {
        $getListParams['filter']['USER_ID'] = $this->userId;

        if ($onlyActual)
            $getListParams['filter']['>=TOKEN_FINAL_DATE'] = time();

        return $this->get($getListParams);
    }

    public function saveTokenInfo($tokenInfo)
    {
        $params = [
            'select' => ['ID'],
            'filter' => ['USER_ID' => $this->userId]
        ];

        if ($tokenInfo['login']){
            $params['filter']['LOGIN'] = $tokenInfo['login'];
        }

        $res = (array) $this->get($params);

        $res = current($res);
        if ($res['ID']){
            UserTable::update($res['ID'], [
                'ACCESS_TOKEN' => $tokenInfo['access_token'],
                'TOKEN_EXPIRES_IN' => $tokenInfo['expires_in'],
                'TOKEN_TYPE' => $tokenInfo['token_type'],
            ]);
        }
        else{
            UserTable::add([
                'USER_ID' => $this->userId,
                'LOGIN' => $tokenInfo['login'],
                'ACCESS_TOKEN' => $tokenInfo['access_token'],
                'TOKEN_EXPIRES_IN' => $tokenInfo['expires_in'],
                'TOKEN_TYPE' => $tokenInfo['token_type'],
            ]);
        }

    }

    public function getTokenInfo($login = null, $onlyActual = false)
    {
        $params = [
            'filter' => [
                'USER_ID' => $this->userId
            ]
        ];

        if ($login)
            $params['filter']['=LOGIN'] = $login;

        if ($onlyActual)
            $params['filter']['>=TOKEN_FINAL_DATE'] = time();

        return $this->get($params);
    }

    public function get($getListParams){
        $result = UserTable::getList($getListParams);

        return $result->fetchAll();
    }

}