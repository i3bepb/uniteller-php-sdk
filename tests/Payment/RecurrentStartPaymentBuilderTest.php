<?php

namespace Tmconsulting\Uniteller\Tests\Payment;

use I3bepb\ReflectionForTest\AccessToMethod;
use I3bepb\ReflectionForTest\AccessToProperty;
use Tmconsulting\Uniteller\Payment\RecurrentStartPaymentBuilder;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Payment\RecurrentStartPaymentBuilder
 */
class RecurrentStartPaymentBuilderTest extends TestCase
{
    use AccessToMethod;
    use AccessToProperty;

    public function testIsRecurrentStart()
    {
        $signatureCreator = $this->createMock(SignatureInterface::class);
        $builder = new RecurrentStartPaymentBuilder($signatureCreator);
        $this->assertEquals('1', $this->privateMethodWithParameters($builder, 'isRecurrentStart'));
        $this->assertTrue($this->getProtectedOrPrivatePropertyValue($builder, 'isRecurrentStart'));
    }
}
