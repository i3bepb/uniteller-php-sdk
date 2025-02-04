<?php

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$uniteller = (new \Tmconsulting\Uniteller\Uniteller(true))
    ->withLogger(new \ShowInBrowserLogger())
    ->withHttpTransport(
        new \GuzzleHttp\Client(),
        new \GuzzleHttp\Psr7\HttpFactory(),
        new \GuzzleHttp\Psr7\HttpFactory()
    );
$builder = $uniteller->recurrentContinuePayment();

$builder->setShopId($shopId)
    ->setLogin($login)
    ->setPassword($password)
    ->setOrderId('recurrent_order_5_1')
    ->setSubtotal(20)
    ->setParentOrderId('start_recurrent_order_5');

$results = $builder->process();

var_dump($results);
