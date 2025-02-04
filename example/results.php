<?php

use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Request\Format;
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
$builder = $uniteller->results();


$builder
    ->setShopId($shopId)
    ->setLogin($login)
    ->setPassword($password)
    ->setFormat(Format::CSV)
    ->setSFields([
        SFields::ORDER_NUMBER,
        SFields::BILL_NUMBER,
        SFields::QRC_ID,
        SFields::SBER_ORDER_ID,
        SFields::SUM,
    ])
    ->setStart(\DateTime::createFromFormat('Y-m-d', '2026-05-01'))
    ->setEnd(\DateTime::createFromFormat('Y-m-d', '2026-05-06'));

$results = $builder->process();

var_dump($results);