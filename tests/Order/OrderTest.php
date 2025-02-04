<?php

namespace Tmconsulting\Uniteller\Tests\Order;

use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Order\Status;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Order\Order
 */
class OrderTest extends TestCase
{
    /**
     * @var \Tmconsulting\Uniteller\Order\Order
     */
    private $order;

    protected function setUp(): void
    {
        $this->order = new Order();
    }

    public static function dataProviderSetAndGetDate(): array
    {
        return [
            ['10.03.2023 15:42:42', '2023-03-10'],
            ['10-01-2025 15:42:42', '2025-01-10'],
            ['2025-01-09 15:42:42', '2025-01-09'],
        ];
    }

    /**
     * @param string|null $set
     * @param $get
     *
     * @dataProvider dataProviderSetAndGetDate
     */
    public function testSetAndGetDate(?string $set, $get)
    {
        $this->order->setDate($set);
        $this->assertInstanceOf(\DateTime::class, $this->order->getDate());
        $this->assertEquals($get, $this->order->getDate()->format('Y-m-d'));
    }

    /**
     * @param string|null $set
     * @param $get
     *
     * @dataProvider dataProviderSetAndGetDate
     */
    public function testSetAndGetPacketDate(?string $set, $get)
    {
        $this->order->setPacketDate($set);
        $this->assertInstanceOf(\DateTime::class, $this->order->getPacketDate());
        $this->assertEquals($get, $this->order->getPacketDate()->format('Y-m-d'));
    }

    public function testSettersAndGetters()
    {
        // Test basic string fields
        $this->order->setAddress('123 Main St');
        $this->assertEquals('123 Main St', $this->order->getAddress());

        $this->order->setApprovalCode('APPROV123');
        $this->assertEquals('APPROV123', $this->order->getApprovalCode());

        // Test numeric fields
        $this->order->setBillNumber(123456);
        $this->assertEquals(123456, $this->order->getBillNumber());

        // Test boolean fields
        $this->order->setCvc2(true);
        $this->assertTrue($this->order->isCvc2());

        $this->order->withoutCvc2();
        $this->assertFalse($this->order->isCvc2());

        $this->order->withCvc2();
        $this->assertTrue($this->order->isCvc2());

        // Test array field
        $eOrderData = 'param1=value1, param2=value2';
        $this->order->setEOrderData($eOrderData);
        $this->assertEquals([
            'param1' => 'value1',
            'param2' => 'value2'
        ], $this->order->getEOrderData());

        // Test status resolution
        $this->order->setStatus('paid');
        $this->assertEquals(Status::resolve('paid'), $this->order->getStatus());
    }

    public function testCardMethods()
    {
        $this->order->setCardNumber('411111******1111');
        $this->assertEquals('411111******1111', $this->order->getCardNumber());

        $this->order->setCardType('visa');
        $this->assertEquals('visa', $this->order->getCardType());

        $this->order->setCardSubType('debit');
        $this->assertEquals('debit', $this->order->getCardSubType());
    }

    public function testCustomerInfoMethods()
    {
        $this->order->setFirstName('John');
        $this->order->setLastName('Doe');
        $this->order->setMiddleName('Smith');
        $this->order->setEmail('john.doe@example.com');
        $this->order->setPhone('+1234567890');

        $this->assertEquals('John', $this->order->getFirstName());
        $this->assertEquals('Doe', $this->order->getLastName());
        $this->assertEquals('Smith', $this->order->getMiddleName());
        $this->assertEquals('john.doe@example.com', $this->order->getEmail());
        $this->assertEquals('+1234567890', $this->order->getPhone());
    }

    public function testPaymentInfoMethods()
    {
        $this->order->setPaymentType(1);
        $this->assertEquals(1, $this->order->getPaymentType());

        $this->order->setTotal('100.50');
        $this->assertEquals(100.50, $this->order->getTotal());

        $this->order->setSum('99.99');
        $this->assertEquals(99.99, $this->order->getSum());

        $this->order->setCurrency('USD');
        $this->assertEquals('USD', $this->order->getCurrency());
    }

    public function testOrderRelations()
    {
        $this->order->setOrderNumber('ORDER123');
        $this->order->setParentOrderNumber('PARENT123');
        $this->order->setSberOrderId('SBER123');

        $this->assertEquals('ORDER123', $this->order->getOrderNumber());
        $this->assertEquals('PARENT123', $this->order->getParentOrderNumber());
        $this->assertEquals('SBER123', $this->order->getSberOrderId());
    }

    public function testErrorHandling()
    {
        $this->order->setErrorCode(123);
        $this->order->setErrorComment('Test error');
        $this->order->setMessage('Error occurred');
        $this->order->setResponseCode('05');
        $this->order->setRecommendation('Contact your bank');

        $this->assertEquals(123, $this->order->getErrorCode());
        $this->assertEquals('Test error', $this->order->getErrorComment());
        $this->assertEquals('Error occurred', $this->order->getMessage());
        $this->assertEquals('05', $this->order->getResponseCode());
        $this->assertEquals('Contact your bank', $this->order->getRecommendation());
    }

    public function testToArrayMethod()
    {
        $this->order->setOrderNumber('TEST123')
            ->setStatus('paid')
            ->setTotal('100.00')
            ->setPaymentType(1)
            ->setCardNumber('411111******1111')
            ->setAddress('123 Main St')
            ->setApprovalCode('APPROV123')
            ->setCvc2(true);

        $result = $this->order->toArray();

        $this->assertArrayHasKey('OrderNumber', $result);
        $this->assertArrayHasKey('Status', $result);
        $this->assertArrayHasKey('Total', $result);
        $this->assertArrayHasKey('PaymentType', $result);
        $this->assertArrayHasKey('CardNumber', $result);
        $this->assertArrayHasKey('CVC2', $result);

        $this->assertEquals('TEST123', $result['OrderNumber']);
        $this->assertEquals(Status::resolve('paid'), $result['Status']);
        $this->assertEquals(100.00, $result['Total']);
    }

    public function testEmptyDateHandling()
    {
        $this->order->setDate(null);
        $this->assertNull($this->order->getDate());

        $this->order->setDate('');
        $this->assertNull($this->order->getDate());
    }

    public function testEmptyEOrderDataHandling()
    {
        $this->order->setEOrderData('');
        $this->assertEquals([], $this->order->getEOrderData());
    }

    public function testAdditionalFields()
    {
        $this->order->setCountry('US');
        $this->order->setRate('1.0');
        $this->order->setProtocolTypeName('HTTP');
        $this->order->setProcessingName('Uniteller');
        $this->order->setAcquirerID('ACQ123');
        $this->order->setIsOtherCard(true);
        $this->order->setQrcId('QR123');

        $this->assertEquals('US', $this->order->getCountry());
        $this->assertEquals('1.0', $this->order->getRate());
        $this->assertEquals('HTTP', $this->order->getProtocolTypeName());
        $this->assertEquals('Uniteller', $this->order->getProcessingName());
        $this->assertEquals('ACQ123', $this->order->getAcquirerID());
        $this->assertTrue($this->order->isOtherCard());
        $this->assertEquals('QR123', $this->order->getQrcId());
    }
}