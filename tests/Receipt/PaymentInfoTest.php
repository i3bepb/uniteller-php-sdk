<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use Tmconsulting\Uniteller\Receipt\PaymentInfo;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\PaymentInfo
 */
class PaymentInfoTest extends TestCase
{
    public function testJsonSerialize()
    {
        $paymentInfo = new PaymentInfo(3, 4, 200.75, 'ABC123');
        $this->assertInstanceOf(PaymentInfo::class, $paymentInfo);
        $data = $paymentInfo->jsonSerialize();
        $this->assertEquals(3, $data['kind']);
        $this->assertEquals(4, $data['type']);
        $this->assertEquals(200.75, $data['amount']);
        $this->assertEquals('ABC123', $data['id']);
    }
}
