<?php

namespace Tmconsulting\Uniteller\Tests\Payment;

use I3bepb\ReflectionForTest\AccessToMethod;
use I3bepb\ReflectionForTest\AccessToProperty;
use Tmconsulting\Uniteller\Builder\Enum\BaseUri;
use Tmconsulting\Uniteller\Payment\PreAuthPaymentWithReceiptBuilder;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Payment\PreAuthPaymentWithReceiptBuilder
 */
class PreAuthPaymentWithReceiptBuilderTest extends TestCase
{
    use AccessToMethod;
    use AccessToProperty;

    private $builder;

    protected function setUp(): void
    {
        $signatureCreator = $this->createMock(SignatureInterface::class);
        $this->builder = new PreAuthPaymentWithReceiptBuilder($signatureCreator);
    }

    public function testGetPreAuth()
    {
        $this->assertEquals('1', $this->privateMethodWithParameters($this->builder, 'isPreAuth'));
        $this->assertTrue($this->getProtectedOrPrivatePropertyValue($this->builder, 'preAuth'));
    }

    public function testGetBaseUri()
    {
        $this->assertEquals(BaseUri::FISCAL, $this->builder->getBaseUri());
    }

    public function testGetRequestName()
    {
        $this->assertEquals(ApiEndpoints::FISCAL_PREAUTH_PAYMENT_WITH_ADVANCE_RECEIPT, $this->builder->getEndpoint());
    }
}
