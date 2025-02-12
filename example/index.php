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
    ->setOrderIdp(10)
    ->setSubtotalP(10)
    ->setMeanType(MeanType::VISA)
    ->setEMoneyType(EMoneyType::ANY)
    ->setLifetime(100)
    ->setMerchantOrderId('59c93853-4ba5-4552-b225-cc3ee0fe40ad')
    ->setCallbackFields([CallbackFields::CARD_IDP, CallbackFields::CARD_NUMBER, CallbackFields::BILL_NUMBER])
    ->setOrderLifetime(120)
    ->setPhone('+79630400529')
//    ->setPaymentTypeLimits([
//        PaymentType::BANK_CARD => [50, 50],
//    ])
//    ->setPhoneVerified('+79630400529')
    ->setUrlReturnOk('https://google.ru/?q=success')
    ->setUrlReturnNo('https://google.ru/?q=failure')
    ->setEmail('g3bepb@gmail.com')
    ->usePreAuth();


//$log = new \Monolog\Logger('name');
//$formatter = new \Monolog\Formatter\LineFormatter(
//    null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
//    null, // Datetime format
//    true, // allowInlineLineBreaks option, default false
//    true  // ignoreEmptyContextAndExtra option, default false
//);
//$debugHandler = new \Monolog\Handler\StreamHandler('out.log', \Monolog\Logger::DEBUG);
//$debugHandler->setFormatter($formatter);
//$log->pushHandler($debugHandler);

$uri = (new Client(new \MyLogger()))->payment($builder)->getUri();

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

