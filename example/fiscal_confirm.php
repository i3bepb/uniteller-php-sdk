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
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Uniteller;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$uniteller = (new Uniteller(true))
    ->withLogger(new \ShowInBrowserLogger())
    ->withLoggingPolicy(new \Tmconsulting\Uniteller\Support\LoggingPolicy(false))
    ->withHttpTransport(
        new \GuzzleHttp\Client(),
        new \GuzzleHttp\Psr7\HttpFactory(),
        new \GuzzleHttp\Psr7\HttpFactory()
    );
$builder = $uniteller->fiscalConfirm();

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
$sum = '50.00';
$item = new Item('АэроБилет', $sum, 1, Unit::PIECE, $sum, Vat::FIVE, Payattr::FULL_PAYMENT, Lineattr::SERVICE, null);
$payment = new PaymentInfo(Kind::CARD, TypePaymentMethod::WITHOUT_ADDITIONAL, $sum);
$receipt = new Receipt(
    Taxmode::SIMPLIFIED_INCOME_MINUS_EXPENSES,
    [$item],
    [$payment],
    $sum
);

$builder->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('fiscal_preauth_order_without_receipt_9')
    ->setFormat(Format::CSV)
    ->setSubtotal($sum)
    ->setReceipt($receipt);

$results = $builder->process();

$r = print_r($results, true);

echo <<< HTML
    <h2>Response</h2>
    <pre>
    $r
    </pre>
    <br>
HTML;
