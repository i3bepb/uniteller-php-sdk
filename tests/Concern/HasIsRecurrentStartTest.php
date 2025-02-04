<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasIsRecurrentStart;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart
 */
class HasIsRecurrentStartTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию признак IsRecurrentStart выключен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::isRecurrentStart
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::getIsRecurrentStart
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasIsRecurrentStart;
        };

        $this->assertFalse($object->isRecurrentStart());
        $this->assertNull($object->getIsRecurrentStart());
    }

    /**
     * Проверяет, что метод setIsRecurrentStart() возвращает текущий объект.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::setIsRecurrentStart
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasIsRecurrentStart;
        };

        $this->assertSame($object, $object->setIsRecurrentStart());
    }

    /**
     * Проверяет, что метод setIsRecurrentStart() без аргументов включает признак.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::setIsRecurrentStart
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::isRecurrentStart
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::getIsRecurrentStart
     */
    public function testSetIsRecurrentStartEnabledByDefault()
    {
        $object = new class {
            use HasIsRecurrentStart;
        };

        $object->setIsRecurrentStart();

        $this->assertTrue($object->isRecurrentStart());
        $this->assertSame('1', $object->getIsRecurrentStart());
    }

    /**
     * Проверяет, что метод setIsRecurrentStart(true) включает признак.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::setIsRecurrentStart
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::isRecurrentStart
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::getIsRecurrentStart
     */
    public function testSetIsRecurrentStartTrue()
    {
        $object = new class {
            use HasIsRecurrentStart;
        };

        $object->setIsRecurrentStart(true);

        $this->assertTrue($object->isRecurrentStart());
        $this->assertSame('1', $object->getIsRecurrentStart());
    }

    /**
     * Проверяет, что метод setIsRecurrentStart(false) выключает признак.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::setIsRecurrentStart
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::isRecurrentStart
     * @covers \Tmconsulting\Uniteller\Concern\HasIsRecurrentStart::getIsRecurrentStart
     */
    public function testSetIsRecurrentStartFalse()
    {
        $object = new class {
            use HasIsRecurrentStart;
        };

        $object->setIsRecurrentStart(true);
        $object->setIsRecurrentStart(false);

        $this->assertFalse($object->isRecurrentStart());
        $this->assertNull($object->getIsRecurrentStart());
    }
}
