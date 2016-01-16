<?php
// 1. Подключение модуля
use Bitrix\Main\Loader;
Loader::includeModule('shantilab.yandexdirect');

// 2. Шаг указание псевдонимов
use Shantilab\YandexDirect\User\Auth;
use Shantilab\YandexDirect\Account\Account;
use Shantilab\YandexDirect\Account\AccountCollection;

// 3. Шаг Авторизация (а) получение временного токена
$yAuth = new Auth();
echo '<a href="' . $yAuth->getAuthorizeUrl() . '">Получить токен</a>';

//4. Шаг обработка временного токена и получение постоянного токена
$yAuth = new Auth();

// получение токена по коду
if ($_REQUEST['code']){
    $yAuth['code'] = intval($_REQUEST['code']);
    $tokenInfo = $yAuth->getToken();
    if ($tokenInfo['access_token']){
        $authInfo = Auth::getInfo($tokenInfo['access_token']);
        //Сохранение в базу (или обновление)
        (new Account([
            'USER_ID' => $USER->GetId(),
            'LOGIN' => $authInfo['login'],
            'ACCESS_TOKEN' => $tokenInfo['access_token'],
            'TOKEN_EXPIRES_IN' => $tokenInfo['expires_in'],
            'TOKEN_TYPE' => $tokenInfo['token_type'],
        ]))->save();

        //Редирект, чтобы нельзя было обновить страницу
        LocalRedirect('/test/');
    }
}

//5. Шаг работа с API
$account = new Account();
$account->getBy(['LOGIN' => 'dir.direct123']); // по умолчанию подставляется текущий USER_ID и происходит проверка на актуальный токен

if (isset($account->getToken())){ // здесь имеется в виду что токен получен из базы и пройдена проверка на его актуальность
    //работа с API
    $result = $account->GetClientInfo(['dir.direct123']);
}

//6. Работа с коллекцией аккаунтов текущего пользователя
$accountCollection = new AccountCollection();
foreach($accountCollection as $collection){
    $collection->GetClientInfo(['dir.direct123']);
}