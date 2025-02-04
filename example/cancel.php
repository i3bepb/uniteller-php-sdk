<?php

use Tmconsulting\Uniteller\Receipt\Enum\Kind;
use Tmconsulting\Uniteller\Receipt\Enum\Lineattr;
use Tmconsulting\Uniteller\Receipt\Enum\Payattr;
use Tmconsulting\Uniteller\Receipt\Enum\Taxmode;
use Tmconsulting\Uniteller\Receipt\Enum\TypePaymentMethod;
use Tmconsulting\Uniteller\Receipt\Enum\Unit;
use Tmconsulting\Uniteller\Receipt\Enum\Vat;
use Tmconsulting\Uniteller\Receipt\Item;
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
$builder = $uniteller->cancel();

$sum = 10;
$item = new Item('Демонстрация', $sum, 1, Unit::PIECE, $sum, Vat::FIVE, Payattr::FULL_PAYMENT, Lineattr::SERVICE, null);
$payment = new PaymentInfo(Kind::CARD, TypePaymentMethod::WITHOUT_ADDITIONAL, $sum);
$receipt = new Receipt(
    Taxmode::SIMPLIFIED_INCOME_MINUS_EXPENSES,
    [$item],
    [$payment],
    $sum
);

$builder->setShopId($shopId)
    ->setPassword($password)
    ->setSubtotal($sum)
    ->setReceipt($receipt)
    ->setOrderId('preauth_order_10');

$results = $builder->process();

var_dump($results);
