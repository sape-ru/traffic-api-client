<?php

require_once 'vendor/autoload.php';

$login = 'YourLogin';
$token = 'a0d962dbcc73...';

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
