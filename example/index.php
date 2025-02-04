<?php

use Tmconsulting\Uniteller\Client;
use Tmconsulting\Uniteller\Payment\CallbackFields;
use Tmconsulting\Uniteller\Payment\EMoneyType;
use Tmconsulting\Uniteller\Payment\MeanType;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Payment\PaymentType;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$builder = (new PaymentBuilder())
    ->setShopIdp($shopId)
    ->setPassword($password)
    ->setOrderIdp(999)
    ->setSubtotalP(50)
    ->setMeanType(MeanType::VISA)
    ->setEMoneyType(EMoneyType::ANY)
    ->setLifetime(100)
    ->setMerchantOrderId('59c93853-4ba5-4552-b225-cc3ee0fe40ad')
    ->setCallbackFields([CallbackFields::CARD_IDP, CallbackFields::CARD_NUMBER, CallbackFields::BILL_NUMBER])
    ->setOrderLifetime(120)
    ->setPhone('+79630400529')
    ->setPaymentTypeLimits([
        PaymentType::BANK_CARD => [50, 50],
    ])
    ->setPhoneVerified('+79630400529')
    ->setUrlReturnOk('http://google.ru/?q=success')
    ->setUrlReturnNo('http://google.ru/?q=failure');

$uri = (new Client())->payment($builder)->getUri();

echo <<< HTML
    <h2>Client Payment Sample</h2>
    <br>
    <p>Оплатить</p>
    <a href="{$uri}" target="_blank">{$uri}</a>
    <br>
    <p>Отмена</p>
    <a href="/cancel.php">/cancel.php</a>
    <br>
    <br>
    <p>Результаты платежа</p>
    <a href="/results.php">/results.php</a>
    <br>
HTML;

// or...

/*

 $uniteller->payment([
    'Shop_IDP' => '',
    // ...
]);

*/

