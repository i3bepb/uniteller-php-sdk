<?php

namespace Tmconsulting\Uniteller\Tests\Cancel;

use Tmconsulting\Uniteller\Cancel\CancelBuilder;
use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\Currency;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Parameter\Enum\Language;
use Tmconsulting\Uniteller\Parameter\Enum\RVRReason;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Cancel\CancelBuilder
 */
class CancelBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new CancelBuilder(new Signature());
    }

    public static function dataProviderGetBillNumber(): array
    {
        return [
            ['504915179825', 504915179825],
            [1798257, 1798257],
            ['', 0],
            ['abc', 0],
            [null, null],
        ];
    }

    /**
     * @param $set
     * @param $get
     *
     * @dataProvider dataProviderGetBillNumber
     *
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::setBillNumber
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::getBillNumber
     */
    public function testSetAndGetBillNumber($set, $get)
    {
        if ($set !== null) {
            $this->builder->setBillNumber($set);
        }
        $this->assertEquals($get, $this->builder->getBillNumber());
    }

    public static function dataProviderGetOrderId(): array
    {
        return [
            ['504', '504'],
            [1798257, '1798257'],
            ['', ''],
            ['abc', 'abc'],
            [null, null],
        ];
    }

    /**
     * @param $set
     * @param $get
     *
     * @dataProvider dataProviderGetOrderId
     *
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::setOrderId
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::getOrderId
     */
    public function testSetAndGetOrderId($set, $get)
    {
        if ($set !== null) {
            $this->builder->setOrderId($set);
        }
        $this->assertEquals($get, $this->builder->getOrderId());
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
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::setCurrency
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::getCurrency
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

    public static function dataProviderGetRvrReason(): array
    {
        return [
            [10, null, true],
            [RVRReason::SHOP, RVRReason::SHOP, false],
            [RVRReason::FRAUD, RVRReason::FRAUD, false],
            [RVRReason::CARDHOLDER, RVRReason::CARDHOLDER, false],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGetRvrReason
     *
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::setRvrReason
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::getRvrReason
     */
    public function testSetAndGetRvrReason($set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
        }
        if ($set !== null) {
            $this->builder->setRvrReason($set);
        }
        $this->assertEquals($get, $this->builder->getRvrReason());
    }

    public static function dataProviderGetLanguage(): array
    {
        return [
            [10, null, true],
            [Language::EN, Language::EN, false],
            [Language::RU, Language::RU, false],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGetLanguage
     *
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::setLanguage
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::getLanguage
     */
    public function testSetAndGetLanguage($set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
        }
        if ($set !== null) {
            $this->builder->setLanguage($set);
        }
        $this->assertEquals($get, $this->builder->getLanguage());
    }

    public static function dataProviderGetFields(): array
    {
        return [
            [null, null],
            [['abc', 'field2'], 'abc;field2'],
        ];
    }

    /**
     * @param $set
     * @param $get
     *
     * @dataProvider dataProviderGetFields
     *
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::setFields
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelBuilder::getFields
     */
    public function testSetAndGetFields($set, $get)
    {
        if ($set !== null) {
            $this->builder->setFields($set);
        }
        $this->assertEquals($get, $this->builder->getFields());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Cancel\CancelBuilder::toArray
     */
    public function testBuildObject()
    {
        $this->builder->setShopId('00999')
            ->setLogin('login')
            ->setPassword('password')
            ->setBillNumber(1)
            ->setCurrency('RUB')
            ->setRvrReason(RVRReason::SHOP)
            ->setFields([])
            ->setSubtotalP(10);

        $expected = [
            'Shop_ID'    => '00999',
            'Login'      => 'login',
            'Password'   => 'password',
            'Billnumber' => 1,
            'Subtotal_P' => (float)10,
            'Currency'   => 'RUB',
            'RVRReason'  => RVRReason::SHOP,
            'Format'     => 1,
            'S_FIELDS'   => ''
        ];

        $this->assertEquals($expected, $this->builder->toArray());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Cancel\CancelBuilder::toArray
     */
    public function testNotSetNotExistToArray()
    {
        $this->builder->setShopId('009999')
            ->setOrderId('sdf1233')
            ->setPassword('password');
        $arr = $this->builder->toArray();
        $this->assertArrayNotHasKey(UnitellerParameterName::BILLNUMBER, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::SHOP_ID, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::ORDER_ID, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::LOGIN, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::PASSWORD, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::SUBTOTAL_P, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::CURRENCY, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::LANGUAGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::FORMAT, $arr);
        /**
         * У запроса отмены два кейса по номеру заказа клиента и по номеру платежа в системе Uniteller.
         * Когда выставлен номер заказа, то Login не используется
         */
        $this->assertArrayNotHasKey(UnitellerParameterName::LOGIN, $this->builder->toArray());
        $this->builder->setLogin('login');
        $this->assertArrayNotHasKey(UnitellerParameterName::LOGIN, $this->builder->toArray());

        /**
         * Когда выставлен номер заказа, то Password не используется
         */
        $this->assertArrayNotHasKey(UnitellerParameterName::PASSWORD, $this->builder->toArray());
        $this->builder->setPassword('password');
        $this->assertArrayNotHasKey(UnitellerParameterName::PASSWORD, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::SUBTOTAL_P, $this->builder->toArray());
        $this->builder->setSubtotalP('10.05');
        $this->assertArrayHasKey(UnitellerParameterName::SUBTOTAL_P, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::CURRENCY, $this->builder->toArray());
        $this->builder->setCurrency(Currency::RUB);
        $this->assertArrayHasKey(UnitellerParameterName::CURRENCY, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::LANGUAGE, $this->builder->toArray());
        $this->builder->setLanguage(Language::RU);
        $this->assertArrayHasKey(UnitellerParameterName::LANGUAGE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::S_FIELDS, $this->builder->toArray());
        $this->builder->setFields([SFields::ORDER_NUMBER]);
        $this->assertArrayHasKey(UnitellerParameterName::S_FIELDS, $this->builder->toArray());

        /**
         * Если выставлен номер платежа в системе Uniteller, тогда также используются Login Password
         */
        $this->builder->setBillNumber('5566324');
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::BILLNUMBER, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::LOGIN, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::PASSWORD, $arr);
    }

    /**
     * @covers \Tmconsulting\Uniteller\Cancel\CancelBuilder::getSignature
     */
    public function testGetSignature()
    {
        $this->builder->setOrderId('order9')
            ->setShopId('2134423')
            ->setPassword('password');
        $this->assertEquals(
            '19D30DC1E20FA4896EE4D7AF1B718513F1FE393346C07EF7BFD0E0E74D03E58A',
            $this->builder->getSignature()
        );
        $this->builder->setSubtotalP(20);
        $this->assertEquals(
            '46B2B80B2D9D22A7EC058A4B96077D9326172735A27BD7C5A1D7533C0B89A8CB',
            $this->builder->getSignature()
        );
        $this->builder->setLanguage(Language::EN);
        $this->assertEquals(
            '4CD1692633E32C45472677BCBE2378627272A031F6CF040255BD6158541EFBE9',
            $this->builder->getSignature()
        );
    }

    /**
     * @covers \Tmconsulting\Uniteller\Cancel\CancelBuilder::getRequestName
     */
    public function testGetRequestName()
    {
        $this->assertEquals(ApiEndpoints::CANCEL, $this->builder->getEndpoint());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Cancel\CancelBuilder::process
     */
    public function testProcess()
    {
        $requestManager = $this->createMock(RequestManager::class);
        $requestManager->method('executeRequestAndParseResponseOrders')->willReturn('success');
        $requestManager->method('setOptions')->willReturn($requestManager);
        $container = new Container([
            \Psr\Http\Client\ClientInterface::class          => \GuzzleHttp\Client::class,
            \Psr\Http\Message\RequestFactoryInterface::class => \GuzzleHttp\Psr7\HttpFactory::class,
            \Psr\Http\Message\StreamFactoryInterface::class  => \GuzzleHttp\Psr7\HttpFactory::class,
            RequestManager::class                            => $requestManager,
        ]);
        $this->builder->setContainer($container);

        $result = $this->builder->process();

        $this->assertEquals('success', $result);
    }
}
