<?php

use Tmconsulting\Uniteller\Callback\Callback;
use Tmconsulting\Uniteller\Dependency\Container;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$log = new \Monolog\Logger('name');
$formatter = new \Monolog\Formatter\LineFormatter(
    null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
    null, // Datetime format
    true, // allowInlineLineBreaks option, default false
    true  // ignoreEmptyContextAndExtra option, default false
);
$debugHandler = new \Monolog\Handler\StreamHandler('out.log', \Monolog\Logger::DEBUG);
$debugHandler->setFormatter($formatter);
$log->pushHandler($debugHandler);

$unitellerDependencyContainer = new Container([
    \Psr\Log\LoggerInterface::class                  => $log,
    \Psr\Http\Client\ClientInterface::class          => \GuzzleHttp\Client::class,
    \Psr\Http\Message\RequestFactoryInterface::class => \GuzzleHttp\Psr7\HttpFactory::class,
    \Psr\Http\Message\StreamFactoryInterface::class  => \GuzzleHttp\Psr7\HttpFactory::class,
], true);

try {
    $orderCallback = $unitellerDependencyContainer->get(Callback::class)
        ->setPassword($password)
        ->process();
} catch (\RuntimeException $e) {
    http_response_code($e->getCode());
    exit($e->getMessage());
}

$log->debug('Ok!', $orderCallback->toArray());
echo 'Ok!';