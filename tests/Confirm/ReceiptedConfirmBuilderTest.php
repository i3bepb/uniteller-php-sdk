<?php

namespace Tmconsulting\Uniteller\Tests\Confirm;

use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
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
use Tmconsulting\Uniteller\Request\ParserCsv;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
 */
class ReceiptedConfirmBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new FiscalConfirmBuilder(new Signature());
    }

    public static function dataProviderGetOrderId(): array
    {
        return [
            ['504', '504', false],
            [1798257, '1798257', false],
            ['', '', true],
            ['abc', 'abc', false],
            [null, null, true],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception
     *
     * @dataProvider dataProviderGetOrderId
     *
     * @covers       \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::setOrderId
     * @covers       \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::getOrderId
     */
    public function testSetAndGetOrderId($set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(RequiredParameterException::class);
            $this->expectExceptionMessage('Parameter OrderID empty or not set');
        }
        if ($set !== null) {
            $this->builder->setOrderId($set);
        }
        $this->assertEquals($get, $this->builder->getOrderId());
    }

    public static function dataProviderGetReceipt(): array
    {
        $item = new Item('Демонстрация', 1, 2, Unit::PIECE, 2, Vat::FIVE, Payattr::FULL_PAYMENT, Lineattr::SERVICE, null);
        $payment = new PaymentInfo(Kind::CARD, TypePaymentMethod::WITHOUT_ADDITIONAL, 2);
        $receipt = new Receipt(
            Taxmode::SIMPLIFIED_INCOME_MINUS_EXPENSES,
            [$item],
            [$payment],
            2
        );
        return [
            [$receipt, $receipt],
        ];
    }

    /**
     * @param $set
     * @param $get
     *
     * @dataProvider dataProviderGetReceipt
     *
     * @covers       \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::setReceipt
     * @covers       \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::getReceipt
     */
    public function testSetAndGetReceipt($set, $get)
    {
        if ($set !== null) {
            $this->builder->setReceipt($set);
        }
        $this->assertEquals($get, $this->builder->getReceipt());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::getReceipt
     */
    public function testGetReceiptThrowsExceptionWhenNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->builder->getReceipt();
    }

    public static function dataProviderGetSubtotalP(): array
    {
        return [
            ['100', 100, false],
            [100, 100, false],
            [100.5, 100.5, false],
            ['', 0, true],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception
     *
     * @dataProvider dataProviderGetSubtotalP
     *
     * @covers       \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::setSubtotalP
     * @covers       \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::getSubtotalP
     */
    public function testSetAndGetSubtotalP($set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(RequiredParameterException::class);
            $this->expectExceptionMessage('Parameter Subtotal empty or not set');
        }
        if ($set !== null) {
            $this->builder->setSubtotalP($set);
        }
        $this->assertEquals($get, $this->builder->getSubtotalP());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::getSignature
     */
    public function testGetSignature()
    {
        $this->builder->setOrderId('order9')
            ->setShopId('2134423')
            ->setSubtotalP(20)
            ->setPassword('password');
        $this->assertEquals(
            '29DE4E939628FF7E54CA7E815A4193B6',
            $this->builder->getSignature()
        );
    }

    /**
     * @covers \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::toArray
     */
    public function testToArray()
    {
        $shopId = '123';
        $orderId = 'order123';
        $subtotalP = 100.50;
        $password = 'password';
        $item = new Item('Демонстрация', 1, 2, Unit::PIECE, 2, Vat::FIVE, Payattr::FULL_PAYMENT, Lineattr::SERVICE, null);
        $payment = new PaymentInfo(Kind::CARD, TypePaymentMethod::WITHOUT_ADDITIONAL, 2);
        $receipt = new Receipt(
            Taxmode::SIMPLIFIED_INCOME_MINUS_EXPENSES,
            [$item],
            [$payment],
            2
        );

        $this->builder
            ->setPassword($password)
            ->setShopId($shopId)
            ->setOrderId($orderId)
            ->setSubtotalP($subtotalP)
            ->setReceipt($receipt);

        $expectedArray = [
            UnitellerParameterName::SHOPID            => $shopId,
            UnitellerParameterName::ORDER_ID          => $orderId,
            UnitellerParameterName::SUBTOTAL          => $subtotalP,
            UnitellerParameterName::SIGNATURE         => $this->builder->getSignature(),
            UnitellerParameterName::RECEIPT           => $receipt->toBase64(),
            UnitellerParameterName::RECEIPT_SIGNATURE => $this->builder->getReceiptSignature(),
        ];

        $this->assertEquals($expectedArray, $this->builder->toArray());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder::process
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
        $requestManager->method('executeRequestAndParseResponseReceipt')->willReturn('success');
        $container->method('get')->willReturn($requestManager);
        $container->method('set')->willReturn(ParserCsv::class);

        $this->builder->setLogger($logger);
        $this->builder->setContainer($container);

        $result = $this->builder->process();

        $this->assertEquals('success', $result);
    }
}
