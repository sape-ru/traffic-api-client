# Клиент к API traffic.sape.ru на PHP

Установка
---------

Выполните:
```
composer require sape/traffic-api-client
```

Получение токена
----------------
Перейдите в паспорт по ссылке https://passport.sape.ru/security/token/ и нажмите на "сгенерировать".
После чего Вы получите сообщение с токеном:
```
Ваш новый токен: a0d962dbcc73e358efc782bb26327d51c5fa81f839eb14065131a50bc144b201
```

Пример использования
--------------------
```php
<?php
require_once 'vendor/autoload.php';

$login = 'YourLogin'; // Ваш логин в traffic.sape.ru
$token = 'a0d962dbcc73...'; // Ваш токен полученный на странице паспорта

$client = new \SapeRt\Api\Client\User;

$logger  = new \Monolog\Logger('traffic');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(STDOUT));

$client->setLogger($logger);

$data = $client->dictionary_dict(true);

echo var_export($data, true) . "\n";

try {
    $res = $client->system_login($login, $token);
} catch (\SapeRt\Api\Exception\AppException $e) {
    echo $e->getMessage();
}
```

Тесты
-----

Запуск тестов
```
./run-tests.sh
```

Документация
------------

Описание методов АПИ:
https://traffic.sape.ru/doc/api

Методы в классе клиента **\SapeRt\Api\Client\User** соответсвуют частям путей после **/api/user** HTTP-запросов из 
[документации](https://traffic.sape.ru/doc/api).

Методы строятся по принципу:

В документации - /api/user/**объект**/**действие-над-объектом**  
В классе \SapeRt\Api\Client\User - **объект**_**действиеНадОбъектом** 

Например:
```
/api/user/ads/check-url-availability
ads_checkUrlAvailability
```
