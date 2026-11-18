<?php

use Tmconsulting\Uniteller\Receipt\Enum\AgentAttribute;
use Tmconsulting\Uniteller\Receipt\Enum\Kind;
use Tmconsulting\Uniteller\Receipt\Enum\Lineattr;
use Tmconsulting\Uniteller\Receipt\Enum\Payattr;
use Tmconsulting\Uniteller\Receipt\Enum\Taxmode;
use Tmconsulting\Uniteller\Receipt\Enum\TypePaymentMethod;
use Tmconsulting\Uniteller\Receipt\Enum\Unit;
use Tmconsulting\Uniteller\Receipt\Enum\Vat;
use Tmconsulting\Uniteller\Receipt\Item\Agent;
use Tmconsulting\Uniteller\Receipt\Item;
use Tmconsulting\Uniteller\Receipt\Customer;
use Tmconsulting\Uniteller\Receipt\Params;
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

// Данные исходного заказа
$orderId = 'simple_order_32';
$sum = '50.00';

// Покупатель
$customer = new Customer('+79051234505', 'karlson@example.com', 'ae3301b9-a103-5a64-9081-72ce065a56fc', 'Карл');

// Агентские реквизиты первых четырёх позиций
$agent = new Agent(
    AgentAttribute::AGENT,
    '+79000000000',
    null,
    null,
    null,
    null,
    null,
    null,
    'АО «Ред Вингс»',
    '7732107883',
    '+78003509977'
);

// Позиции чека возврата
$items = [
    // Билет 1 — тариф
    new Item(
        'Авиаперевозка, билет №3096114443445 - Тариф',
        '43.30',
        1,
        Unit::PIECE,
        '43.30',
        Vat::ZERO,
        Payattr::FULL_PAYMENT,
        Lineattr::SERVICE,
        null,
        $agent
    ),

    // Билет 1 — агентский сбор
    new Item(
        'Авиаперевозка, билет №3096114443445 - Агентский сбор (AG)',
        '0.70',
        1,
        Unit::PIECE,
        '0.70',
        Vat::NO_VAT,
        Payattr::FULL_PAYMENT,
        Lineattr::SERVICE,
        null,
        $agent
    ),


    // Сервисный сбор — без агентских реквизитов
    new Item(
        'Сервисный сбор',
        '6.00',
        1,
        Unit::PIECE,
        '6.00',
        Vat::NO_VAT,
        Payattr::FULL_PAYMENT,
        Lineattr::SERVICE,
        null
    ),
];

$payment = new PaymentInfo(
    Kind::CARD,
    TypePaymentMethod::WITHOUT_ADDITIONAL,
    $sum
);

// Чек возврата прихода
$receipt = new Receipt(
    Taxmode::STANDARD,
    $items,
    [$payment],
    $sum,
    'help@aerotur.aero',
    null,
    $customer,
    null,
    1,
    null,
    null,
    new Params('https://nemo.aerotur.aero')
);

// Запрос полной отмены
$builder = $uniteller->fiscalCancel();

$builder->setShopId($shopId)
    ->setPassword($password)
    ->setReceipt($receipt)
    ->setSubtotal('50.00')
    ->setOrderId($orderId);

// Subtotal при полной отмене с Receipt не передаётся.
// Требуется исправление FiscalCancelBuilder ниже.

$results = $builder->process();

var_dump($results);
