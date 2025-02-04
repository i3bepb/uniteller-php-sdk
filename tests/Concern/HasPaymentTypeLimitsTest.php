<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasPaymentTypeLimits;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasPaymentTypeLimits
 */
class HasPaymentTypeLimitsTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию PaymentTypeLimits не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPaymentTypeLimits::hasPaymentTypeLimits
     * @covers \Tmconsulting\Uniteller\Concern\HasPaymentTypeLimits::getPaymentTypeLimits
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasPaymentTypeLimits;
        };

        $this->assertFalse($object->hasPaymentTypeLimits());
        $this->assertNull($object->getPaymentTypeLimits());
    }

    /**
     * Проверяет fluent interface при передаче строки (уже сериализованный JSON).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPaymentTypeLimits::setPaymentTypeLimits
     */
    public function testSetReturnsSelfWithString()
    {
        $object = new class {
            use HasPaymentTypeLimits;
        };

        $this->assertSame($object, $object->setPaymentTypeLimits('{}'));
    }

    /**
     * Проверяет, что массив кодируется в JSON для параметра запроса.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPaymentTypeLimits::setPaymentTypeLimits
     * @covers \Tmconsulting\Uniteller\Concern\HasPaymentTypeLimits::hasPaymentTypeLimits
     * @covers \Tmconsulting\Uniteller\Concern\HasPaymentTypeLimits::getPaymentTypeLimits
     */
    public function testSetPaymentTypeLimitsEncodesArrayAsJson()
    {
        $object = new class {
            use HasPaymentTypeLimits;
        };

        $object->setPaymentTypeLimits(['a' => [1, 2]]);
        $this->assertTrue($object->hasPaymentTypeLimits());
        $this->assertSame('{"a":[1,2]}', $object->getPaymentTypeLimits());
    }
}
