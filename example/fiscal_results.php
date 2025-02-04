<?php

use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Uniteller;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$uniteller = (new Uniteller())
    ->withLogger(new \ShowInBrowserLogger())
    ->withHttpTransport(
        new \GuzzleHttp\Client(),
        new \GuzzleHttp\Psr7\HttpFactory(),
        new \GuzzleHttp\Psr7\HttpFactory()
    );
$builder = $uniteller->fiscalResults();

$builder->setShopId($shopId)
    ->setLogin($login)
    ->setPassword($password)
    ->setFormat(Format::CSV)
    ->setHeader1(1)
    ->setOrderId('2605180001_7');
//    ->setSFields([
//        SFields::ORDER_NUMBER,
//        SFields::BILL_NUMBER,
//        SFields::QRC_ID,
//        SFields::SBER_ORDER_ID,
//        SFields::SUM,
//    ])
//    ->setStart(\DateTime::createFromFormat('Y-m-d', '2026-05-11'))
//    ->setEnd(\DateTime::createFromFormat('Y-m-d', '2026-05-14'));

$results = $builder->process();

$r = print_r($results, true);

echo <<< HTML
    <h2>Results</h2>
    <pre>
    $r
    </pre>
    <br>
HTML;
