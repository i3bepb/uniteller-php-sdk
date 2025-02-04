<?php

use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;
use Tmconsulting\Uniteller\Uniteller;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password, $email, $phone;

$uniteller = (new Uniteller(true))->withLogger(new \ShowInBrowserLogger());
$builder = $uniteller->payment();

/**
 * Оплата
 */
$builder->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('simple_order_25')
    ->setSubtotal(20)
    ->setLifetime(360)
    ->setOrderLifetime(360)
    ->setCallbackFields([
        CallbackFields::CARD_IDP,
        CallbackFields::CARD_NUMBER,
        CallbackFields::BILL_NUMBER,
    ])
    ->setPhone($phone)
    ->setPhoneVerified($phone)
    ->setUrlReturnOk('https://google.ru/?q=success')
    ->setUrlReturnNo('https://google.ru/?q=failure')
    ->setEmail($email);


$uri = $builder->process()->getUri();

echo <<< HTML
    <h2>Простая оплата</h2>
    <br>
    <a href="{$uri}" target="_blank">{$uri}</a>
    <br>
HTML;
