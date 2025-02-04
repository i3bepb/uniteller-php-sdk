<?php

namespace Tmconsulting\Uniteller\Tests\Order;

use Tmconsulting\Uniteller\Order\CallbackOrder;
use Tmconsulting\Uniteller\Order\Status;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Order\CallbackOrder
 */
class CallbackOrderTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $order = new CallbackOrder(
            'order123',
            'authorized',
            'acq123',
            'approv456',
            789,
            'card123',
            '411111******1111',
            'cust789',
            '5',
            'webmoney',
            1,
            100.50,
            100.50
        );

        $this->assertEquals('order123', $order->getOrderId());
        $this->assertEquals(Status::resolve('authorized'), $order->getStatus());
        $this->assertEquals('acq123', $order->getAcquirerId());
        $this->assertEquals('approv456', $order->getApprovalCode());
        $this->assertEquals(789, $order->getBillNumber());
        $this->assertEquals('card123', $order->getCardId());
        $this->assertEquals('411111******1111', $order->getCardNumber());
        $this->assertEquals('cust789', $order->getCustomerId());
        $this->assertEquals('5', $order->getEci());
        $this->assertEquals('webmoney', $order->getEMoneyType());
        $this->assertEquals(1, $order->getPaymentType());
        $this->assertEquals(100.50, $order->getTotal());
        $this->assertEquals(100.50, $order->getBalance());
    }

    public function testConstructorWithMinimumRequiredFields()
    {
        $order = new CallbackOrder('order456', 'paid');

        $this->assertEquals('order456', $order->getOrderId());
        $this->assertEquals(Status::resolve('paid'), $order->getStatus());
        $this->assertNull($order->getAcquirerId());
        $this->assertNull($order->getApprovalCode());
        $this->assertNull($order->getBillNumber());
        $this->assertNull($order->getCardId());
        $this->assertNull($order->getCardNumber());
        $this->assertNull($order->getCustomerId());
        $this->assertNull($order->getEci());
        $this->assertNull($order->getEMoneyType());
        $this->assertNull($order->getPaymentType());
        $this->assertNull($order->getTotal());
        $this->assertNull($order->getBalance());
    }

    public function testSetSignature()
    {
        $order = new CallbackOrder('order789', 'refunded');
        $result = $order->setSignature('test_signature');

        $this->assertEquals('test_signature', $order->getSignature());
        $this->assertSame($order, $result);
    }

    public function testToArrayWithAllFields()
    {
        $order = new CallbackOrder(
            'order123',
            'authorized',
            'acq123',
            'approv456',
            789,
            'card123',
            '411111******1111',
            'cust789',
            '5',
            'webmoney',
            1,
            100.50,
            100.50
        );

        $expected = [
            'Order_ID'     => 'order123',
            'Status'       => Status::resolve('authorized'),
            'AcquirerID'   => 'acq123',
            'ApprovalCode' => 'approv456',
            'BillNumber'   => 789,
            'CardNumber'   => '411111******1111',
            'Card_IDP'     => 'card123',
            'Customer_IDP' => 'cust789',
            'ECI'          => '5',
            'EMoneyType'   => 'webmoney',
            'PaymentType'  => 1,
            'Total'        => 100.50,
            'Balance'      => 100.50,
        ];

        $this->assertEquals($expected, $order->toArray());
    }

    public function testToArrayWithMinimumFields()
    {
        $order = new CallbackOrder('order456', 'paid');

        $expected = [
            'Order_ID' => 'order456',
            'Status'   => Status::resolve('paid'),
        ];

        $this->assertEquals($expected, $order->toArray());
    }

    public function testToArrayWithSomeOptionalFields()
    {
        $order = new CallbackOrder(
            'order789',
            'cancelled',
            null,
            null,
            123,
            null,
            '511111******1111',
            null,
            null,
            null,
            2,
            50.25,
            0.00
        );

        $expected = [
            'Order_ID'    => 'order789',
            'Status'      => Status::resolve('cancelled'),
            'BillNumber'  => 123,
            'CardNumber'  => '511111******1111',
            'PaymentType' => 2,
            'Total'       => 50.25,
            'Balance'     => 0.00,
        ];

        $this->assertEquals($expected, $order->toArray());
    }
}
