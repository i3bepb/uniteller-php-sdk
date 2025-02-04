<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasMeanType;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\MeanType;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasMeanType
 */
class HasMeanTypeTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле MeanType не установлено
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMeanType::hasMeanType
     * @covers \Tmconsulting\Uniteller\Concern\HasMeanType::getMeanType
     */
    public function testHasMeanTypeReturnsFalseByDefault()
    {
        $object = new class {
            use HasMeanType;
        };

        $this->assertFalse($object->hasMeanType());
        $this->assertNull($object->getMeanType());
    }

    /**
     * Проверяет, что метод setMeanType() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMeanType::setMeanType
     */
    public function testSetMeanTypeReturnsSelf()
    {
        $object = new class {
            use HasMeanType;
        };
        $result = $object->setMeanType(MeanType::VISA);

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setMeanType() принимает null
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMeanType::hasMeanType
     * @covers \Tmconsulting\Uniteller\Concern\HasMeanType::getMeanType
     */
    public function testSetMeanTypeAcceptsNull()
    {
        $object = new class {
            use HasMeanType;
        };
        $object->setMeanType(null);

        $this->assertFalse($object->hasMeanType());
        $this->assertNull($object->getMeanType());
    }

    /**
     * Невалидные значения для MeanType:
     * - значения вне допустимого диапазона
     */
    public static function dataProviderSetMeanTypeInvalid(): array
    {
        return [
            'negative'        => [-1],
            'above range'     => [6],
            'far above range' => [100],
        ];
    }

    /**
     * Проверяет, что метод setMeanType() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetMeanTypeInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMeanType::setMeanType
     */
    public function testSetMeanTypeInvalid($val)
    {
        $object = new class {
            use HasMeanType;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setMeanType($val);
    }

    /**
     * Валидные значения для MeanType:
     * - null
     * - все допустимые значения из enum MeanType
     */
    public static function dataProviderSetMeanTypeValid(): array
    {
        return [
            'null'             => [null],
            'any card'         => [MeanType::ANY_CARD],
            'visa'             => [MeanType::VISA],
            'mastercard'       => [MeanType::MASTERCARD],
            'diners club'      => [MeanType::DINERS_CLUB],
            'jcb'              => [MeanType::JCB],
            'american express' => [MeanType::AMERICAN_EXPRESS],
        ];
    }

    /**
     * Проверяет, что метод setMeanType() корректно принимает валидные значения:
     * - для null: hasMeanType() возвращает false
     * - для int: hasMeanType() возвращает true
     * - getMeanType() возвращает то же значение, которое было передано
     *
     * @dataProvider dataProviderSetMeanTypeValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMeanType::hasMeanType
     * @covers \Tmconsulting\Uniteller\Concern\HasMeanType::getMeanType
     */
    public function testSetMeanTypeValid($val)
    {
        $object = new class {
            use HasMeanType;
        };
        $object->setMeanType($val);

        $this->assertSame($val, $object->getMeanType());
        if ($val !== null) {
            $this->assertTrue($object->hasMeanType());
        } else {
            $this->assertFalse($object->hasMeanType());
        }
    }
}
