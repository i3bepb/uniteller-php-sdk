<?php

use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password, $email;

/**
 * Оплата с преавторизацией и фискализацией (без чека аванса)
 */
$uniteller = (new \Tmconsulting\Uniteller\Uniteller(true))->withLogger(new \ShowInBrowserLogger());
$builder = $uniteller->fiscalPreAuthPayment();

$builder->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('fiscal_preauth_order_without_receipt_9')
    ->setSubtotal('50.00')
    ->setLifetime(360)
    ->setUrlReturnOk('https://google.com/?q=success')
    ->setUrlReturnNo('https://google.com/?q=failure')
    ->setCallbackFields(CallbackFields::toArray())
    ->setEmail($email);

$uriWithReceipt = $builder->generateUrl()->getUrl();

echo <<< HTML
    <p>Оплата с преавторизацией и фискализацией</p>
    <a href="{$uriWithReceipt}" target="_blank">{$uriWithReceipt}</a>
    <br>
    <p>Подтверждение оплаты с фискализацией</p>
    <a href="/fiscal_confirm.php">/fiscal_confirm.php</a>
    <br>
    <p>Отмена</p>
    <a href="/fiscal_cancel.php">/fiscal_cancel.php</a>
    <br>
HTML;
