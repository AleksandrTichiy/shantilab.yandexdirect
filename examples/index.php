<?php
// 1. Подключение модуля
use Bitrix\Main\Loader;
Loader::includeModule('shantilab.yandexdirect');

// 2. Шаг указание псевдонимов
use Shantilab\YandexDirect\User\Auth;
use Shantilab\YandexDirect\Api;
use Shantilab\YandexDirect\User\AccountsCollection as AcColl;

// 3. Шаг Авторизация (а) получение временного токена
$yAuth = new Auth();
echo '<a href="' . $yAuth->getAuthorizeUrl() . '">Получить токен</a>';

//4. Шаг обработка временного токена и получение постоянного токена
if (isset($_REQUEST['code'])){
    $code = $_REQUEST['code'];
    $yAuth->setParams(['code' => $code]);
    $tokenInfo = $yAuth->getToken();

    //Сохранение в базу пользователя с логином и токеном
    if ($tokenInfo['access_token']){
        $authInfo = Auth::getInfo($tokenInfo['access_token']);
        $accounts = new AcColl();
        $accounts->saveTokenInfo($tokenInfo + $authInfo); // сам обновит если нужно

        LocalRedirect('/test/');
    }
}

//5. Шаг работа с API
$accounts = new AcColl();
$data = current($accounts->getTokenInfo($login = 'dir.direct123', $onlyActual = true));

if (isset($data['ACCESS_TOKEN'])){ // здесь имеется в виду что токен получен из базы и пройдена проверка на его актуальность
    $yApi = new Api(['token' => $data['ACCESS_TOKEN']]);
    $result = $yApi->GetClientInfo(['dir.direct123']);
}