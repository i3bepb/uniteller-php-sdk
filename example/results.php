<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

use Tmconsulting\Uniteller\Client;
use Tmconsulting\Uniteller\Results\ResultsBuilder;

require __DIR__ . '/credentials.php';

global $shopId, $login, $password;

$builder = (new ResultsBuilder())
    ->setShopIdp($shopId)
    ->setPassword($password)
    ->setOrderIdp(10);

$results = (new Client(new \MyLogger()))->results($builder);

var_dump($results);
