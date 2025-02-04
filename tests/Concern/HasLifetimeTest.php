<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasLifetime;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasLifetime
 */
class HasLifetimeTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию lifetime не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLifetime::hasLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasLifetime::getLifetime
     */
    public function testHasLifetimeReturnsFalseByDefault()
    {
        $object = new class {
            use HasLifetime;
        };

        $this->assertFalse($object->hasLifetime());
        $this->assertNull($object->getLifetime());
    }

    /**
     * Проверяет, что метод setLifetime() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLifetime::setLifetime
     */
    public function testSetLifetimeReturnsSelf()
    {
        $object = new class {
            use HasLifetime;
        };

        $result = $object->setLifetime(100);

        $this->assertSame($object, $result);
    }

    /**
     * Невалидные значения для Lifetime:
     * - ноль
     * - отрицательные значения
     */
    public static function dataProviderSetLifetimeInvalid(): array
    {
        return [
            'zero'           => [0],
            'negative one'   => [-1],
            'negative large' => [-100],
        ];
    }

    /**
     * Проверяет отклонение невалидного времени жизни (не положительное целое).
     *
     * @dataProvider dataProviderSetLifetimeInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLifetime::setLifetime
     */
    public function testSetLifetimeInvalid(int $val)
    {
        $object = new class {
            use HasLifetime;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setLifetime($val);
    }

    /**
     * Валидные значения для Lifetime:
     * - положительные значения
     */
    public static function dataProviderSetLifetimeValid(): array
    {
        return [
            'min value'    => [1],
            'small value'  => [10],
            'medium value' => [300],
            'large value'  => [1000],
        ];
    }

    /**
     * Проверяет приём валидных значений Lifetime.
     *
     * @dataProvider dataProviderSetLifetimeValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLifetime::setLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasLifetime::hasLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasLifetime::getLifetime
     */
    public function testSetLifetimeValid(int $val)
    {
        $object = new class {
            use HasLifetime;
        };

        $object->setLifetime($val);

        $this->assertTrue($object->hasLifetime());
        $this->assertSame($val, $object->getLifetime());
    }
}
