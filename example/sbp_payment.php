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
$builder = $uniteller->sbpPayment();

/**
 * СБП
 */
$builder->setShopId($shopId)
    ->setPassword($password)
    ->setOrderId('sbp_order_test_9')
    ->setSubtotal(2)
    ->setLifetime(300)
    ->setUrlReturnOk('https://i3bepb.ru/?q=success')
    ->setUrlReturnNo('https://i3bepb.ru/?q=failure')
    ->setEmail($email);

$uriFast = $builder->process()->getUri();

echo <<< HTML
    <h2>Оплата СБП</h2>
    <br>
    <a href="{$uriFast}" target="_blank">{$uriFast}</a>
    <br>
HTML;
