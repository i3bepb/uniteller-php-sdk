<?php

use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Dependency\Container;
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

global $shopId, $login, $password;

$uniteller = (new Uniteller(true))
    ->withLogger(new \ShowInBrowserLogger())
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
$sum = 50;
$item = new Item('Билет', $sum, 1, Unit::PIECE, $sum, Vat::FIVE, Payattr::FULL_PAYMENT, Lineattr::SERVICE, null);
$payment = new PaymentInfo(Kind::CARD, TypePaymentMethod::WITHOUT_ADDITIONAL, $sum);
$receipt = new Receipt(
    Taxmode::SIMPLIFIED_INCOME_MINUS_EXPENSES,
    [$item],
    [$payment],
    $sum
);

$builder->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('fiscal_preauth_order_with_receipt_2')
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
