<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasOrderLifetime;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime
 */
class HasOrderLifetimeTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию orderLifetime не установлен
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::hasOrderLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::getOrderLifetime
     */
    public function testHasOrderLifetimeReturnsFalseByDefault()
    {
        $object = new class {
            use HasOrderLifetime;
        };

        $this->assertFalse($object->hasOrderLifetime());
        $this->assertNull($object->getOrderLifetime());
    }

    /**
     * Проверяет, что метод setOrderLifetime() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::setOrderLifetime
     */
    public function testSetOrderLifetimeReturnsSelf()
    {
        $object = new class {
            use HasOrderLifetime;
        };

        $result = $object->setOrderLifetime(300);
        $this->assertSame($object, $result);
    }

    /**
     * Невалидные значения для OrderLifetime:
     * - ноль
     * - отрицательные значения
     */
    public static function dataProviderSetOrderLifetimeInvalid(): array
    {
        return [
            'zero'           => [0],
            'negative one'   => [-1],
            'negative large' => [-100],
        ];
    }

    /**
     * Проверяет, что метод setOrderLifetime() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetOrderLifetimeInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::setOrderLifetime
     */
    public function testSetOrderLifetimeInvalid(int $val)
    {
        $object = new class {
            use HasOrderLifetime;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setOrderLifetime($val);
    }

    /**
     * Валидные значения для OrderLifetime:
     * - положительные значения
     */
    public static function dataProviderSetOrderLifetimeValid(): array
    {
        return [
            'min value'        => [1],
            'small value'      => [10],
            'medium value'     => [300],
            'large value'      => [3600],
            'very large value' => [86400],
        ];
    }

    /**
     * Проверяет, что метод setOrderLifetime() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetOrderLifetimeValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::setOrderLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::getOrderLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::hasOrderLifetime
     */
    public function testSetOrderLifetimeValid(int $input)
    {
        $object = new class {
            use HasOrderLifetime;
        };

        $object->setOrderLifetime($input);

        $this->assertTrue($object->hasOrderLifetime());
        $this->assertSame($input, $object->getOrderLifetime());
    }

    /**
     * Проверяем возможность сброса путем set null
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::setOrderLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::getOrderLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderLifetime::hasOrderLifetime
     */
    public function testSetOrderLifetimeNullResetsValue()
    {
        $object = new class {
            use HasOrderLifetime;
        };

        $object->setOrderLifetime(300);
        $this->assertTrue($object->hasOrderLifetime());

        $object->setOrderLifetime(null);

        $this->assertFalse($object->hasOrderLifetime());
        $this->assertNull($object->getOrderLifetime());
    }
}
