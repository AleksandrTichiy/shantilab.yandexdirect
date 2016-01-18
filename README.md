# Yandex Direct Api
---

## Описание

## Установка
- Скопировать репозиторий в папку `/bitrix/modules/` или `/local/modules/`.
- Зайти в административную часть сайта и установить модуль в системе 1С Bitrix.
- Указать `applicationID` и `applicationPassword` в `settings.yml`.
- !!!Обязательно перед использованием указать ключ `secretKey` для шифрования токенов.
- Указать `testMode` в `false` в `settings.yml` для работы на боевом приложении.
- Настроить `settings.yml` для работы.

## Использование
### Подключение модуля
```php
use Bitrix\Main\Loader;
Loader::includeModule('shantilab.yandexdirect');
```

### Указание псевдонимов
```php
use Shantilab\YandexDirect\User\Auth;
use Shantilab\YandexDirect\Account\Account;
use Shantilab\YandexDirect\Account\AccountCollection;
```

### Авторизация / получение временного токена
```php
$yAuth = new Auth();
echo '<a href="' . $yAuth->getAuthorizeUrl() . '">Получить токен</a>';
```

### Обработка временного токена и получение постоянного токена
```php
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
```

### Шаг работа с API
```php
$account = new Account();

// по умолчанию подставляется текущий USER_ID и происходит проверка на актуальный токен
$account->getBy(['LOGIN' => 'dir.direct123']);

// здесь имеется в виду что токен получен из базы и пройдена проверка на его актуальность
if ($account->getToken()){
    //работа с API
    $result = $account->GetClientInfo(['dir.direct123']);
}
```

### Работа с коллекцией аккаунтов текущего пользователя
```php
$accountCollection = new AccountCollection();
foreach($accountCollection as $collection){
    $collection->GetClientInfo(['dir.direct123']);
}
```
### Работа с финансовыми токенами
Для работы с финансовыми токенами, нужно иметь мастер-токен. Его можно хранить в базе, а можно использовать на лету.
Рекомендуется не хранить мастер-токен в базе (хотя возможность такая имеется).
```php
$account = new Account();
$account->getBy(['LOGIN' => 'dir.direct123']);
$account['MASTER_TOKEN'] = 'sdfasdfadsg4resfsrhdf';

/*можно сохранить финансовый токен*/
$account->save();

/*Или использовать сразу*/
$result = $account->GetCreditLimits();
```
Список финансовых операций (операций для которых нжно использовать мастер-токен) указывается в `settings.yml`

## Примечание
Все токены в базе шифруются. Алгоритм шифрования можно изменять