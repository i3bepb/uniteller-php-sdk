<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasPreauth;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasPreauth
 */
class HasPreauthTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию признак Preauth выключен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::isPreauth
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::getPreauth
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasPreauth;
        };

        $this->assertFalse($object->isPreauth());
        $this->assertNull($object->getPreauth());
    }

    /**
     * Проверяет, что метод setPreauth() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::setPreauth
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasPreauth;
        };

        $this->assertSame($object, $object->setPreauth());
    }

    /**
     * Проверяет, что метод setPreauth() без аргументов включает признак преавторизации.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::setPreauth
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::isPreauth
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::getPreauth
     */
    public function testSetPreauthEnabledByDefault()
    {
        $object = new class {
            use HasPreauth;
        };

        $object->setPreauth();

        $this->assertTrue($object->isPreauth());
        $this->assertSame('1', $object->getPreauth());
    }

    /**
     * Проверяет, что метод setPreauth(true) включает признак преавторизации.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::setPreauth
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::isPreauth
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::getPreauth
     */
    public function testSetPreauthTrue()
    {
        $object = new class {
            use HasPreauth;
        };

        $object->setPreauth(true);

        $this->assertTrue($object->isPreauth());
        $this->assertSame('1', $object->getPreauth());
    }

    /**
     * Проверяет, что метод setPreauth(false) выключает признак преавторизации.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::setPreauth
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::isPreauth
     * @covers \Tmconsulting\Uniteller\Concern\HasPreauth::getPreauth
     */
    public function testSetPreauthFalse()
    {
        $object = new class {
            use HasPreauth;
        };

        $object->setPreauth(true);
        $object->setPreauth(false);

        $this->assertFalse($object->isPreauth());
        $this->assertNull($object->getPreauth());
    }
}
