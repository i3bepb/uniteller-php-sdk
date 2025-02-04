<?php

use Tmconsulting\Uniteller\Uniteller;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$uniteller = (new Uniteller(true))
    ->withLogger(new \ShowInBrowserLogger())
    ->withHttpTransport(
        new \GuzzleHttp\Client(),
        new \GuzzleHttp\Psr7\HttpFactory(),
        new \GuzzleHttp\Psr7\HttpFactory()
    );
$builder = $uniteller->confirm();

$builder->setShopId($shopId)
    ->setLogin($login)
    ->setPassword($password)
    ->setBillNumber(611920040511);

$results = $builder->process();

var_dump($results);
