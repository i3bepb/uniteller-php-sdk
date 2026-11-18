<?php

require __DIR__ . '/credentials.php';

global $shopId, $login, $password, $email;

$uniteller = (new \Tmconsulting\Uniteller\Uniteller(true))->withLogger(new \ShowInBrowserLogger());
$builder = $uniteller->preAuthPayment();

/**
 * Оплата с преавторизацией
 */
$builder->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('preauth_order_10')
    ->setSubtotal(10)
    ->setLifetime(360)
    ->setUrlReturnOk('https://google.com/?q=success')
    ->setUrlReturnNo('https://google.com/?q=failure')
    ->setEmail($email);

$uri = $builder->process()->getUrl();

echo <<< HTML
    <p>Оплата с преавторизацией</p>
    <a href="{$uri}" target="_blank">{$uri}</a>
    <br>
    <p>Подтверждение оплаты с преавторизацией</p>
    <a href="/confirm.php">/confirm.php</a>
    <br>
    <p>Отмена</p>
    <a href="/fiscal_cancel.php">/fiscal_cancel.php</a>
    <br>
HTML;
