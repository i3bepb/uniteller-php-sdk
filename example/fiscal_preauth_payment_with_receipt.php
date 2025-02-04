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
    ->setOrderId('fiscal_preauth_order_with_receipt_2')
    ->setSubtotal(50)
    ->setLifetime(360)
    ->setUrlReturnOk('https://i3bepb.ru/?q=success')
    ->setUrlReturnNo('https://i3bepb.ru/?q=failure')
    ->setCallbackFields(CallbackFields::toArray())
    ->setEmail($email);

$uriWithReceipt = $builder->process()->getUri();

echo <<< HTML
    <p>Оплата с преавторизацией и фискализацией (с чеком аванса)</p>
    <a href="{$uriWithReceipt}" target="_blank">{$uriWithReceipt}</a>
    <br>
    <p>Подтверждение оплаты с фискализацией</p>
    <a href="/fiscal_confirm.php">/fiscal_confirm.php</a>
    <br>
    <p>Отмена</p>
    <a href="/cancel.php">/cancel.php</a>
    <br>
HTML;
