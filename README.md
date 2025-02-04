# Uniteller PHP SDK

<br />

<p align="center">
    <img src="https://lk.uniteller.ru/images/ulogo.png" width="128" alt="Uniteller" />
</p>

<br />

<p align="center">
    <a href="https://travis-ci.org/tmconsulting/uniteller-php-sdk" target="_blank">
        <img src="https://travis-ci.org/tmconsulting/uniteller-php-sdk.svg?branch=master" />
    </a>
    <a href="https://packagist.org/packages/tmconsulting/uniteller-php-sdk" target="_blank">
        <img src="https://poser.pugx.org/tmconsulting/uniteller-php-sdk/v/stable" />
    </a>
    <a href="https://packagist.org/packages/tmconsulting/uniteller-php-sdk" target="_blank">
        <img src="https://poser.pugx.org/tmconsulting/uniteller-php-sdk/license" />
    </a>
</p>

<br />

PHP (7.2+) SDK for integration internet-acquiring of the Uniteller (unofficial).
[This documentation is available in Russian language](README_RU.md).
Also, this SDK integrated with [Payum](https://github.com/Payum/Payum) library and you can use [gateway](https://github.com/tmconsulting/payum-uniteller-gateway).

Features:
* payment (method `pay`)
* recurrent (method `recurrent`)
* cancel (method `unblock`)
* receive results
* callback (method for verify incoming signature)
* general error handler for any request
* general statuses (In the requests/responses may to meet `canceled` or `cancelled` variants. They will be converted to general status like as `cancelled`.)

TODO:
* translate to English comments and system (error) messages
* validation
* implement method `card`
* implement method `confirm` 

## Install

For install package follow this command:

`composer require tmconsulting/uniteller-php-sdk`

## Usage

A few usage example the current SDK your can found on the `examples` folder. 
Just follow instruction on `README.md` file. 

### Configure credentials  

```php
<?php
$uniteller = new \Tmconsulting\Uniteller\Client();
$uniteller->setShopId('you_shop_id');
$uniteller->setLogin('you_login_number');
$uniteller->setPassword('you_password');
$uniteller->setBaseUri('https://wpay.uniteller.ru');
```

### Redirect to page payment 

So, for redirect to page your enough to run `payment` method with parameters like as:

```php
<?php
use Tmconsulting\Uniteller\Payment\PaymentBuilder;

$builder = new PaymentBuilder();
$builder
    ->setOrderId(mt_rand(10000, 99999))
    ->setSubtotalP(10)
    ->setCustomerIdp(mt_rand(10000, 99999))
    ->setUrlReturnOk('http://google.ru/?q=success')
    ->setUrlReturnNo('http://google.ru/?q=failure');

$container->payment($builder)->go();
// if you don't need redirect
// $uniteller->payment($builder)->getUri();

```

or use plain array

```php
<?php
$container->payment([
    'Order_IDP' => mt_rand(10000, 99999),
    // ... other parameters
])->go();
```

### Recurrent payment

```php
<?php
use Tmconsulting\Uniteller\Recurrent\RecurrentBuilder;

$builder = (new RecurrentBuilder())
    ->setOrderIdp(mt_rand(10000, 99999))
    ->setSubtotalP(15)
    ->setParentOrderIdp(00000) // order id of any past payment
    ->setParentShopIdp($container->getShopId()); // optional

$results = $container->recurrent($builder);
```

or use plain array

```php
<?php
$results = $container->recurrent([
    'Order_IDP' => mt_rand(10000, 99999),
    // ... other parameters
]);
```

### Cancel payment

```php
<?php
use Tmconsulting\Uniteller\Cancel\CancelBuilder;

$builder = (new CancelBuilder())->setBillNumber('RRN Number, (12 digits)');
$results = $container->cancel($builder);
```

or

```php
<?php
use Tmconsulting\Uniteller\Order\Status;

$results = $container->cancel([
    'Billnumber' => 'RRN Number, (12 digits)',
    // ...
]);

foreach ($results as $payment) {
    // see Tmconsulting\Uniteller\Order\Order for other methods.
    if ($payment->getStatus() === Status::CANCELLED) {
        // payment was cancelled
    }    
} 
```

### Receive results

```php
<?php

$results = $container->results([
    'ShopOrderNumber' => 'Order_IDP number'
]);

var_dump($results);

// $results[0]->getCardNumber();
```

### Callback (gateway notification)

Receive incoming parameters from gateway and verifying signature.

```php
<?php
if (! $container->verifyCallbackRequest(['all_parameters_from_post_with_signature'])) {
    return 'invalid_signature';
}
```

## Tests

`vendor/bin/phpunit`

## License

MIT.

| № | task | status |
| :--- | :--- | :--- |
| 1 | Запрос на оплату\(https://wpay.uniteller.ru/pay/\). Параметры запроса, сигнатура. | Да |
| 2 | Преавторизация платежа. Запрос выше, доработки. | Да |
| 3 | Подтверждение платежа, проведённого с преавторизацией \(https://wpay.uniteller.ru/confirm/\). Параметры запроса, сигнатура. | Да |
| 4 | Отмена платежа и возврат средств \(https://wpay.uniteller.ru/unblock/\). Параметры запроса. | Да |
| 5 | Запрос результата авторизации \(https://wpay.uniteller.ru/results/\) проще говоря список заказов. Параметры запроса. | Да |
| 6 | Уведомление об изменении статуса заказа. Тут и с фиксализацией | Да |
| 7 | Парсинг ответов в csv формате. Обработка ошибок в ответах | При появлении нового запроса, возможно доработка |
| 8 | Парсинг ответов в xml формате. Обработка ошибок в ответах. | Нет |
| 9 | Форма оплаты в iframe. Разобраться, попробовать, что необходимо для этого. | Нет |
| 10 | Платёж в фоновом режиме. Нам не нужен | Нет |
| 11 | Оплата с помощью платёжной ссылки. Нам не нужен | Нет |
| 12 | Рекуррентные платежи \(https://wpay.uniteller.ru/recurrent/\). Связан с Запросом на оплату. Нам не нужен | Да |
| 13 | Регистрация банковских карт и все запросы по просмотру, удалению, блокировке. Нам не нужен | Нет |
| 14 | Запрос оплаты с фискализацией \(https://fpay.uniteller.ru/v2/pay\). Параметры запроса, сигнатура. | Да |
| 15 | Описание чека. | Да |
| 16 | Преавторизация с фискализацией \(https://fpay.uniteller.ru/v2/api/iacheck\). Параметры запроса, сигнатура. | Нет |
| 17 | Отмена платежа с фискализацией \(https://fpay.uniteller.ru/v2/cancel\). Параметры запроса, сигнатура. | Нет |
| 18 | Запрос статуса заказа с фискализацией \(https://fpay.uniteller.ru/v2/results\), короче список заказов. Параметры запроса, сигнатура. | Нет |
| 19 | Оплата Apple Pay через API. Нам не нужен. | Нет |
| 20 | Оплата Google Pay через API. Нам не нужен. | Нет |
| 21 | Платёж в фоновом режиме с фискализацией \(https://fpay.uniteller.ru/v2/api/pay\). Нам не нужен. | Нет |
| 22 | Рекуррентный платёж с фискализацией \(https://fpay.uniteller.ru/v2/recurrent\). Нам не нужен. | Нет |
| 23 | Преавторизация с печатью чека аванса с использованием платёжной формы. Нам не нужен. | Нет |
| 24 | Преавторизация с печатью чека аванса через API. Нам не нужен. | Нет |
| 25 | Преавторизация с печатью чека аванса через упрощённый API. Нам не нужен. | Нет |
| 26 | Подтверждение платежа с преавторизацией с печатью чека аванса. Нам не нужен. | Нет |
| 27 | Запрос регистрации заказа с фискализацией для оплаты с помощью платёжной ссылки \(https://fpay.uniteller.ru/v2/api/register\). Нам не нужен. | Нет |
| 28 | Формирование чека коррекции через упрощённый API \(https://fpay.uniteller.ru/v2/api/correct\). | Нет |
| 29 | Оплата СБП. Доработка первого запроса на оплату | Нет |
