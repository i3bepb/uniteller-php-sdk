<?php

require __DIR__ . '/credentials.php';

global $shopId, $login, $password, $email, $phone;

$uniteller = (new \Tmconsulting\Uniteller\Uniteller(true))->withLogger(new \ShowInBrowserLogger());
$builderRecurrentStart = $uniteller->recurrentStartPayment();

/**
 * Стартовый платеж для рекуррентных платежей
 */
$builderRecurrentStart->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('start_recurrent_order_5')
    ->setSubtotal(20)
    ->setLifetime(360)
    ->setUrlReturnOk('https://google.ru/?q=success')
    ->setUrlReturnNo('https://google.ru/?q=failure')
    ->setEmail($email);

$uriRecurrentStart = $builderRecurrentStart->process()->getUri();

echo <<< HTML
    <h2>Стартовый рекуррентый платеж</h2>
    <br>
    <a href="{$uriRecurrentStart}" target="_blank">{$uriRecurrentStart}</a>
    <br>
HTML;
