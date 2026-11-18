<?php

use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password, $email;

/**
 * Оплата с преавторизацией и фискализацией (с чеком аванса)
 */
$uniteller = (new \Tmconsulting\Uniteller\Uniteller(true))->withLogger(new \ShowInBrowserLogger());
$builder = $uniteller->fiscalPreAuthPaymentWithAdvanceReceipt();

$builder->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('fiscal_preauth_order_with_receipt_7')
    ->setSubtotal('50.00')
    ->setLifetime(360)
    ->setUrlReturnOk('https://google.com/?q=success')
    ->setUrlReturnNo('https://google.com/?q=failure')
    ->setCallbackFields(CallbackFields::toArray())
    ->setEmail($email);

$uriWithReceipt = $builder->process()->getUrl();

echo <<< HTML
    <p>Оплата с преавторизацией и фискализацией (с чеком аванса)</p>
    <a href="{$uriWithReceipt}" target="_blank">{$uriWithReceipt}</a>
    <br>
    <p>Подтверждение оплаты с фискализацией</p>
    <a href="/fiscal_confirm.php">/fiscal_confirm.php</a>
    <br>
    <p>Отмена</p>
    <a href="/fiscal_cancel.php">/fiscal_cancel.php</a>
    <br>
HTML;
