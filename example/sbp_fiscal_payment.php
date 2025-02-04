<?php

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

require __DIR__ . '/credentials.php';

global $shopId, $login, $password, $email, $phone;

$uniteller = (new \Tmconsulting\Uniteller\Uniteller(true))->withLogger(new \ShowInBrowserLogger());
$builder = $uniteller->sbpFiscalPayment();

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

/**
 * СБП
 */
$builder->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('fast_payment_system_order_test_8')
    ->setSubtotal(2)
    ->setLifetime(300)
    ->setUrlReturnOk('https://i3bepb.ru/?q=success')
    ->setUrlReturnNo('https://i3bepb.ru/?q=failure')
    ->setReceipt($receipt)
    ->setEmail($email);

$uriFast = $builder->process()->getUri();

echo <<< HTML
    <h2>Оплата СБП</h2>
    <br>
    <a href="{$uriFast}" target="_blank">{$uriFast}</a>
    <br>
HTML;
