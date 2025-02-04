<?php

use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;
use Tmconsulting\Uniteller\Receipt\Enum\AgentAttribute;
use Tmconsulting\Uniteller\Receipt\Enum\Kind;
use Tmconsulting\Uniteller\Receipt\Enum\Lineattr;
use Tmconsulting\Uniteller\Receipt\Enum\Payattr;
use Tmconsulting\Uniteller\Receipt\Enum\Taxmode;
use Tmconsulting\Uniteller\Receipt\Enum\TypePaymentMethod;
use Tmconsulting\Uniteller\Receipt\Enum\Unit;
use Tmconsulting\Uniteller\Receipt\Enum\Vat;
use Tmconsulting\Uniteller\Receipt\Item;
use Tmconsulting\Uniteller\Receipt\Item\Agent;
use Tmconsulting\Uniteller\Receipt\PaymentInfo;
use Tmconsulting\Uniteller\Receipt\Receipt;
use Tmconsulting\Uniteller\Uniteller;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password, $email, $phone;

/**
 * Оплата с фискализацией
 */
$uniteller = (new Uniteller(true))->withLogger(new \ShowInBrowserLogger());
$builderWithReceipt = $uniteller->fiscalPayment();

/**
 * Данные для чека
 */
$agent = new Agent(
    AgentAttribute::AGENT,
    '+78002000600',
    null,
    null,
    null,
    null,
    null,
    null,
    'О0О «Рога и Копыта»',
    '1234567890',
    '+78002000602'
);
$item = new Item('Демонстрация', 1, 2, Unit::PIECE, 2, Vat::FIVE, Payattr::FULL_PAYMENT, Lineattr::SERVICE, null);
$payment = new PaymentInfo(Kind::CARD, TypePaymentMethod::WITHOUT_ADDITIONAL, 2);
$receipt = new Receipt(
    Taxmode::SIMPLIFIED_INCOME_MINUS_EXPENSES,
    [$item],
    [$payment],
    2
);

$builderWithReceipt->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('receipt_order_15')
    ->setSubtotal(2)
    ->setLifetime(360)
    ->setUrlReturnOk('https://i3bepb.ru/?q=success')
    ->setUrlReturnNo('https://i3bepb.ru/?q=failure')
    ->setReceipt($receipt)
    ->setCallbackFields(CallbackFields::toArray())
    ->setEmail($email);

$uriWithReceipt = $builderWithReceipt->process()->getUri();

echo <<< HTML
    <h2>Оплата с фискализацией</h2>
    <br>
    <a href="{$uriWithReceipt}" target="_blank">{$uriWithReceipt}</a>
    <br>
HTML;
