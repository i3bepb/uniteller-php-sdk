<?php

namespace Tmconsulting\Uniteller\Tests\Cancel;

use Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder;
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
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder
 */
class CancelWithReceiptBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new CancelWithReceiptBuilder(new Signature());
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
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder::setOrderId
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder::getOrderId
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
            [null, null],
        ];
    }

    /**
     * @param $set
     * @param $get
     *
     * @dataProvider dataProviderGetReceipt
     *
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder::setReceipt
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder::getReceipt
     */
    public function testSetAndGetReceipt($set, $get)
    {
        if ($set !== null) {
            $this->builder->setReceipt($set);
        }
        $this->assertEquals($get, $this->builder->getReceipt());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder::getSignature
     */
    public function testGetSignature()
    {
        $this->builder->setOrderId('order9')
            ->setShopId('2134423')
            ->setPassword('password');
        $this->assertEquals(
            'E44DB2A77439171029F6EBAAEF8FB53B651777F3512152471B2FC7D31D4A8AC1',
            $this->builder->getSignature()
        );
        $this->builder->setSubtotalP(20);
        $this->assertEquals(
            'F611CBB1FB4CE9607F9D5F02EED5EEFE0ECC98DA872B5EE8CF95CEC69C66B88E',
            $this->builder->getSignature()
        );
    }

    /**
     * @covers \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder::toArray
     */
    public function testToArray()
    {
        $this->builder->setShopId('12345')
            ->setOrderId('order33')
            ->setPassword('password');
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::UPID, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::ORDER_ID, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::PASSWORD, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::SUBTOTAL_P, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::RECEIPT, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::SIGNATURE, $arr);
        $this->builder->setSubtotalP('10.05');
        $this->assertArrayHasKey(UnitellerParameterName::SUBTOTAL, $this->builder->toArray());
        $item = new Item('Демонстрация', 1, 2, Unit::PIECE, 2, Vat::FIVE, Payattr::FULL_PAYMENT, Lineattr::SERVICE, null);
        $payment = new PaymentInfo(Kind::CARD, TypePaymentMethod::WITHOUT_ADDITIONAL, 2);
        $receipt = new Receipt(
            Taxmode::SIMPLIFIED_INCOME_MINUS_EXPENSES,
            [$item],
            [$payment],
            2
        );
        $this->builder->setReceipt($receipt);
        $this->assertArrayHasKey(UnitellerParameterName::RECEIPT, $this->builder->toArray());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder::getRequestName
     */
    public function testGetRequestName()
    {
        $this->assertEquals(ApiEndpoints::FISCAL_CANCEL, $this->builder->getEndpoint());
    }

    public static function dataProviderGetShopId(): array
    {
        return [
            ['simple', 'simple', false],
            ['', null, true],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGetShopId
     *
     * @covers       \Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder::getShopId
     */
    public function testSetAndGetShopId($set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(RequiredParameterException::class);
            $this->expectExceptionMessage('Parameter UPID empty or not set');
        }
        if ($set !== null) {
            $this->builder->setShopId($set);
        }
        $this->assertEquals($get, $this->builder->getShopId());
    }
}
