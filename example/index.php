<?php

use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\ClientInterface;
use Tmconsulting\Uniteller\Common\BaseUri;
use Tmconsulting\Uniteller\Common\EMoneyType;
use Tmconsulting\Uniteller\Common\MeanType;
use Tmconsulting\Uniteller\Dependency\UnitellerContainer;
use Tmconsulting\Uniteller\Payment\CallbackFields;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Payment\PaymentType;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$uniteller = new UnitellerContainer([LoggerInterface::class => \MyLogger::class]);
$builder = $uniteller->get(PaymentBuilder::class)
    ->setShopId($shopId)
    ->setPassword($password)
    ->setOrderIdp(22)
    ->setSubtotalP(20)
    ->setMeanType(MeanType::VISA)
    ->setEMoneyType(EMoneyType::ANY)
    ->setLifetime(120)
    ->setCallbackFields([CallbackFields::CARD_IDP, CallbackFields::CARD_NUMBER, CallbackFields::BILL_NUMBER])
    ->setPhone('+79630400529')

    // ->setOrderLifetime(10)
    ->setPhoneVerified('+79630400529')
    ->setPaymentTypeLimits([
        PaymentType::BANK_CARD => [20, 20],
    ])
    ->setMerchantOrderId('59c93853-4ba5-4552-b225-cc3ee0fe40ad')

    ->setUrlReturnOk('https://google.ru/?q=success')
    ->setUrlReturnNo('https://google.ru/?q=failure')
    ->setEmail('g3bepb@gmail.com')
    ->usePreAuth();

$client = $uniteller->get(ClientInterface::class);
$uri = $client->payment($builder)->getUri();

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

