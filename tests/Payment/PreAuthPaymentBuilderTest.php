<?php

namespace Tmconsulting\Uniteller\Tests\Payment;

use I3bepb\ReflectionForTest\AccessToMethod;
use I3bepb\ReflectionForTest\AccessToProperty;
use Tmconsulting\Uniteller\Payment\PreAuthPaymentBuilder;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Payment\PreAuthPaymentBuilder
 */
class PreAuthPaymentBuilderTest extends TestCase
{
    use AccessToMethod;
    use AccessToProperty;

    public function testIsPreAuth()
    {
        $signatureCreator = $this->createMock(SignatureInterface::class);
        $builder = new PreAuthPaymentBuilder($signatureCreator);
        $this->assertEquals('1', $this->privateMethodWithParameters($builder, 'isPreAuth'));
        $this->assertTrue($this->getProtectedOrPrivatePropertyValue($builder, 'preAuth'));
    }
}
