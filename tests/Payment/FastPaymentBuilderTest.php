<?php

namespace Tmconsulting\Uniteller\Tests\Payment;

use Tmconsulting\Uniteller\Parameter\Enum\PaymentType;
use Tmconsulting\Uniteller\Payment\FastPaymentBuilder;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Payment\FastPaymentBuilder
 */
class FastPaymentBuilderTest extends TestCase
{
    public function testGetPaymentTypeLimitsReturnsCorrectJson()
    {
        $builder = $this->getMockBuilder(FastPaymentBuilder::class)
            ->onlyMethods(['getSubtotalP'])
            ->disableOriginalConstructor()
            ->getMock();

        $builder->method('getSubtotalP')->willReturn(1500.00);

        $expected = json_encode([
            PaymentType::SBP => [1500, 1500]
        ]);

        $this->assertEquals($expected, $builder->getPaymentTypeLimits());
    }
}
