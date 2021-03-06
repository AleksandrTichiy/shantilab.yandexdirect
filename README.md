# Bitrix module: Yandex Direct Api
---

## Описание
Модуль позволяет использовать API яндекс директа, в том числе и финансовые операции.

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

// получение постоянного токена по временному
if ($_REQUEST['code']){
$yAuth['code'] = intval($_REQUEST['code']);

    $tokenInfo = $yAuth->getToken();
    $errors = ($tokenInfo->getErrors()) ?: [];

    if (!$errors && $tokenInfo['access_token']){
        $authInfo = Auth::getInfo($tokenInfo['access_token']);
        $errors = array_merge($errors, ($authInfo->getErrors()) ?: []);
    }

    if (!$errors){
        //Сохранение в базу (или обновление, если такой аккаунт уже есть в базе)
        (new Account([
            'USER_ID' => $USER->GetId(),
            'LOGIN' => $authInfo['login'],
            'ACCESS_TOKEN' => $tokenInfo['access_token'],
            'TOKEN_EXPIRES_IN' => $tokenInfo['expires_in'],
            'TOKEN_TYPE' => $tokenInfo['token_type'],
        ]))->save();
        
        //Редирект
        LocalRedirect('/');
    }else{
        foreach($errors as $error){
            // Обработка ошибок
        }
    }
}
```

### Работа с API
Для работы с API Яндекс директ необходимо, чтобы был доступен токен (`ACCESS_TOKEN`) в полях экземпляра класса `Account`. 
```php
/* по умолчанию подставляется текущий USER_ID и происходит проверка на актуальный токен.*/
$account = new Account();
$account->getBy(['LOGIN' => 'dir.direct123']);

/* здесь имеется в виду что токен получен из базы и пройдена проверка на его актуальность*/
if ($account->getToken($checkActual = true)){
    /*работа с API. Теперь от экземпляра класса Account можно вызывать любые методы Яндекс Директа*/
    $resultResponse = $account->GetClientInfo(['dir.direct123']);
    foreach($resultResponse->getErrors() as $error){
        if ($error['code'] == 53) //токен устарел
            echo '<a href="' . $yAuth->getAuthorizeUrl() . '">Получить токен</a>';
    }
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
Рекомендуется не хранить мастер-токен в базе (хотя возможность такая имеется). Мастер токен также шифруется.
```php
$account = new Account();
$account->getBy(['LOGIN' => 'dir.direct123']);
$account['MASTER_TOKEN'] = 'sdfasdfadsg4resfsrhdf';

/*можно сохранить финансовый токен*/
$account->save();

/*Или использовать сразу*/
$resultResponse = $account->GetCreditLimits();
```
Список финансовых операций (операций, для которых нужно использовать мастер-токен) указывается в `settings.yml`.

## Примечание
Все токены в базе шифруются. Алгоритм шифрования можно изменять.