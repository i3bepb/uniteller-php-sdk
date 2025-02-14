<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\ClientInterface;
use Tmconsulting\Uniteller\Dependency\UnitellerContainer;
use Tmconsulting\Uniteller\Results\Format;
use Tmconsulting\Uniteller\Results\ResultsBuilder;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$uniteller = new UnitellerContainer([LoggerInterface::class => \MyLogger::class]);

$builder = $uniteller->get(ResultsBuilder::class)
    ->setShopId($shopId)
    ->setLogin($login)
    ->setPassword($password)
    ->setFormat(Format::XML)
    ->setHeader1(1)
    ->setStart(\DateTime::createFromFormat('Y-m-d', '2025-02-10'))
    ->setEnd(\DateTime::createFromFormat('Y-m-d', '2025-02-14'));

$client = $uniteller->get(ClientInterface::class);
$results = $client->results($builder);
