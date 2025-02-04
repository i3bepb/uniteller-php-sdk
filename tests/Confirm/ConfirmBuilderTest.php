<?php

namespace Tmconsulting\Uniteller\Tests\Confirm;

use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Confirm\ConfirmBuilder;
use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\Currency;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Parameter\Enum\Language;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Request\ParserCsv;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Confirm\ConfirmBuilder
 */
class ConfirmBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new ConfirmBuilder(new Signature());
    }

    public static function dataProviderGetBillNumber(): array
    {
        return [
            ['100500', 100500, false],
            [100500, 100500, false],
            ['', 0, true],
            [0, 0, true],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGetBillNumber
     *
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::setBillNumber
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::getBillNumber
     */
    public function testSetAndGetBillNumber($set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(RequiredParameterException::class);
            $this->expectExceptionMessage('Parameter Billnumber empty or not set');
        }
        if ($set !== null) {
            $this->builder->setBillNumber($set);
        }
        $this->assertEquals($get, $this->builder->getBillNumber());
    }

    public static function dataProviderGetSubtotalP(): array
    {
        return [
            ['100', 100],
            [100, 100],
            [100.5, 100.5],
            ['', 0],
        ];
    }

    /**
     * @param $set
     * @param $get
     *
     * @dataProvider dataProviderGetSubtotalP
     *
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::setSubtotalP
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::getSubtotalP
     */
    public function testSetAndGetSubtotalP($set, $get)
    {
        if ($set !== null) {
            $this->builder->setSubtotalP($set);
        }
        $this->assertEquals($get, $this->builder->getSubtotalP());
    }

    public static function dataProviderGetCurrency(): array
    {
        return [
            [null, null, false],
            ['dollar', null, true],
            [Currency::RUB, Currency::RUB, false],
            [Currency::USD, Currency::USD, false],
            [Currency::AZN, Currency::AZN, false],
            [Currency::EUR, Currency::EUR, false],
            [Currency::KGS, Currency::KGS, false],
            [Currency::KZT, Currency::KZT, false],
            [Currency::UAH, Currency::UAH, false],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGetCurrency
     *
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::setCurrency
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::getCurrency
     */
    public function testSetAndGetCurrency($set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
        }
        if ($set !== null) {
            $this->builder->setCurrency($set);
        }
        $this->assertEquals($get, $this->builder->getCurrency());
    }

    public static function dataProviderGetLanguage(): array
    {
        return [
            [null, null, false],
            ['kzn', null, true],
            [Language::RU, Language::RU, false],
            [Language::EN, Language::EN, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetLanguage
     *
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::setLanguage
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::getLanguage
     */
    public function testSetAndGetLanguage($set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage(
                'Not valid parameter Language, must be one of the values: ' . implode(',', Language::toArray())
            );
        }
        if ($set !== null) {
            $this->builder->setLanguage($set);
        }
        $this->assertEquals($get, $this->builder->getLanguage());
    }

    /**
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::setFields
     * @covers       \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::getFields
     */
    public function testSetAndGetFields()
    {
        $fields = [SFields::ORDER_NUMBER, SFields::STATUS];
        $this->builder->setFields($fields);
        $this->assertEquals(implode(';', $fields), $this->builder->getFields());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::toArray
     */
    public function testToArray()
    {
        $billNumber = 123456789012;
        $login = 'user';
        $shopId = 'order9';
        $password = 'password';
        $subtotalP = 100.50;
        $currency = Currency::USD;
        $language = Language::EN;
        $fields = [SFields::ORDER_NUMBER, SFields::STATUS];

        $this->builder
            ->setBillNumber($billNumber)
            ->setLogin($login)
            ->setShopId($shopId)
            ->setPassword($password)
            ->setSubtotalP($subtotalP)
            ->setCurrency($currency)
            ->setLanguage($language)
            ->setFields($fields);

        $expectedArray = [
            UnitellerParameterName::BILLNUMBER => $billNumber,
            UnitellerParameterName::SHOP_ID    => $shopId,
            UnitellerParameterName::LOGIN      => $login,
            UnitellerParameterName::PASSWORD   => $password,
            UnitellerParameterName::SUBTOTAL_P => $subtotalP,
            UnitellerParameterName::CURRENCY   => $currency,
            UnitellerParameterName::LANGUAGE   => $language,
            UnitellerParameterName::FORMAT     => 1,
            UnitellerParameterName::S_FIELDS   => implode(';', $fields),
        ];

        $this->assertEquals($expectedArray, $this->builder->toArray());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Confirm\ConfirmBuilder::process
     *
     * @throws \Throwable
     */
    public function testProcess()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $container = $this->createMock(Container::class);
        $requestManager = $this->createMock(RequestManager::class);
        $this->createMock(ParserInterface::class);

        $requestManager->method('setOptions')->willReturn($requestManager);
        $requestManager->method('executeRequestAndParseResponseOrders')->willReturn('success');
        $container->method('get')->willReturn($requestManager);
        $container->method('set')->willReturn(ParserCsv::class);

        $this->builder->setLogger($logger);
        $this->builder->setContainer($container);

        $result = $this->builder->process();

        $this->assertEquals('success', $result);
    }
}
