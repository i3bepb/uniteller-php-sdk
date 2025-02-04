<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasSubtotalP;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP
 */
class HasSubtotalPTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле Subtotal_P не установлено:
     * - hasSubtotalP() возвращает false
     * - getSubtotalP() возвращает null
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP::hasSubtotalP
     * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP::getSubtotalP
     */
    public function testHasSubtotalPReturnsFalseByDefault()
    {
        $object = new class {
            use HasSubtotalP;
        };

        $this->assertFalse($object->hasSubtotalP());
        $this->assertNull($object->getSubtotalP());
    }

    /**
     * Проверяет, что метод setSubtotalP() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP::setSubtotalP
     */
    public function testSetSubtotalPReturnsSelf()
    {
        $object = new class {
            use HasSubtotalP;
        };
        $result = $object->setSubtotalP('12.34');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setSubtotalP() принимает целое число:
     * - hasSubtotalP() возвращает true
     * - getSubtotalP() возвращает установленное значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP::hasSubtotalP
     * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP::getSubtotalP
     */
    public function testSetSubtotalPAcceptsInteger()
    {
        $object = new class {
            use HasSubtotalP;
        };
        $object->setSubtotalP(12);

        $this->assertTrue($object->hasSubtotalP());
        $this->assertSame(12, $object->getSubtotalP());
    }

    /**
     * Невалидные значения для Subtotal_P:
     * - неподдерживаемые типы
     * - пустые строки
     * - строки только из пробелов
     * - нулевые и отрицательные значения
     * - некорректный формат числа
     */
    public static function dataProviderSetSubtotalPInvalid(): array
    {
        return [
            // Булево значение true
            'bool true' => [true],
            // Булево значение false
            'bool false' => [false],
            // Пустая строка
            'empty string' => [''],
            // Строка из пробела
            'space only' => [' '],
            // Нулевое значение (int)
            'zero int' => [0],
            // Нулевое значение (string)
            'zero string' => ['0'],
            // Нулевое значение с десятичной частью
            'zero decimal' => ['0.00'],
            // Отрицательное значение (int)
            'negative int' => [-1],
            // Некорректный формат числа (без ведущего нуля)
            'missing leading zero' => [.50],
            // Число с плавающей точкой (float)
            'float' => [12.34],
            // Массив вместо числа
            'array' => [array(12.34)],
            // Использование запятой вместо точки
            'comma as decimal separator' => ['12,34'],
            // Пробелы по краям строки
            'leading and trailing spaces' => [' 12.34  '],
            // Более 2 знаков после точки
            'too many decimals' => ['12.345'],
            // Отрицательное значение в строке
            'negative string' => ['-12.34'],
        ];
    }

    /**
     * Проверяет, что метод setSubtotalP() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetSubtotalPInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP::setSubtotalP
     */
    public function testSetSubtotalPInvalid($val)
    {
        $object = new class {
            use HasSubtotalP;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setSubtotalP($val);
    }

    /**
     * Валидные значения для Subtotal_P:
     * - целые числа
     * - строки с целыми числами
     * - строки с 1 или 2 знаками после десятичной точки
     */
    public static function dataProviderSetSubtotalPValid(): array
    {
        return [
            // Целое число (int)
            'integer' => [12],
            // Целое число в строке
            'integer string' => ['12'],
            // Один знак после точки
            'one decimal place' => ['12.3'],
            // Два знака после точки
            'two decimal places' => ['12.34'],
        ];
    }

    /**
     * Проверяет, что метод setSubtotalP() корректно принимает валидные значения:
     * - hasSubtotalP() возвращает true
     * - getSubtotalP() возвращает то же значение, которое было передано
     *
     * @dataProvider dataProviderSetSubtotalPValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP::hasSubtotalP
     * @covers \Tmconsulting\Uniteller\Concern\HasSubtotalP::getSubtotalP
     */
    public function testSetSubtotalPValid($val)
    {
        $object = new class {
            use HasSubtotalP;
        };
        $object->setSubtotalP($val);

        $this->assertTrue($object->hasSubtotalP());
        $this->assertSame($val, $object->getSubtotalP());
    }
}
