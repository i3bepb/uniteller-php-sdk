<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasBillLifetime;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime
 */
class HasBillLifetimeTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию BillLifetime не установлен
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::hasBillLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::getBillLifetime
     */
    public function testHasBillLifetimeReturnsFalseByDefault()
    {
        $object = new class {
            use HasBillLifetime;
        };

        $this->assertFalse($object->hasBillLifetime());
        $this->assertNull($object->getBillLifetime());
    }

    /**
     * Проверяет, что метод setBillLifetime() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::setBillLifetime
     */
    public function testSetBillLifetimeReturnsSelf()
    {
        $object = new class {
            use HasBillLifetime;
        };

        $result = $object->setBillLifetime(72);

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что setBillLifetime(null) сбрасывает значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::setBillLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::hasBillLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::getBillLifetime
     */
    public function testSetBillLifetimeNullResetsValue()
    {
        $object = new class {
            use HasBillLifetime;
        };

        $object->setBillLifetime(72);

        $this->assertTrue($object->hasBillLifetime());
        $this->assertSame(72, $object->getBillLifetime());

        $object->setBillLifetime(null);

        $this->assertFalse($object->hasBillLifetime());
        $this->assertNull($object->getBillLifetime());
    }

    /**
     * Невалидные значения для BillLifetime:
     * - меньше 1
     * - больше 1080
     */
    public static function dataProviderSetBillLifetimeInvalid(): array
    {
        return [
            // Значение меньше минимально допустимого (1)
            'zero' => [0],
            // Отрицательное значение
            'negative' => [-1],
            // Значение больше максимально допустимого (1080)
            'above max' => [1081],
            // Значительно больше максимума
            'far above max' => [2000],
        ];
    }

    /**
     * Проверяет, что метод setBillLifetime() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetBillLifetimeInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::setBillLifetime
     */
    public function testSetBillLifetimeInvalid(int $val)
    {
        $object = new class {
            use HasBillLifetime;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setBillLifetime($val);
    }

    /**
     * Валидные значения для BillLifetime:
     * - минимальное допустимое значение
     * - типичное значение
     * - максимальное допустимое значение
     */
    public static function dataProviderSetBillLifetimeValid(): array
    {
        return [
            // Минимально допустимое значение
            'min value' => [1],
            // Типичное значение
            'typical value' => [72],
            // Максимально допустимое значение
            'max value' => [1080],
        ];
    }

    /**
     * Проверяет, что метод setBillLifetime() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetBillLifetimeValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::setBillLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::hasBillLifetime
     * @covers \Tmconsulting\Uniteller\Concern\HasBillLifetime::getBillLifetime
     */
    public function testSetBillLifetimeValid(int $input)
    {
        $object = new class {
            use HasBillLifetime;
        };

        $object->setBillLifetime($input);

        $this->assertTrue($object->hasBillLifetime());
        $this->assertSame($input, $object->getBillLifetime());
    }
}
