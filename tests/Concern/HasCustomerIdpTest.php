<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasCustomerIdp;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp
 */
class HasCustomerIdpTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию Customer_IDP не установлен
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::hasCustomerIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::getCustomerIdp
     */
    public function testHasCustomerIdpReturnsFalseByDefault()
    {
        $object = new class {
            use HasCustomerIdp;
        };

        $this->assertFalse($object->hasCustomerIdp());
        $this->assertNull($object->getCustomerIdp());
    }

    /**
     * Проверяет, что метод setCustomerIdp() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::setCustomerIdp
     */
    public function testSetCustomerIdpReturnsSelf()
    {
        $object = new class {
            use HasCustomerIdp;
        };

        $result = $object->setCustomerIdp('customer-123');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что setCustomerIdp(null) сбрасывает значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::setCustomerIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::hasCustomerIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::getCustomerIdp
     */
    public function testSetCustomerIdpNullResetsValue()
    {
        $object = new class {
            use HasCustomerIdp;
        };

        $object->setCustomerIdp('customer-123');
        $this->assertTrue($object->hasCustomerIdp());
        $this->assertSame('customer-123', $object->getCustomerIdp());

        $object->setCustomerIdp(null);
        $this->assertFalse($object->hasCustomerIdp());
        $this->assertNull($object->getCustomerIdp());
    }

    /**
     * Невалидные значения для Customer_IDP:
     * - неподдерживаемые типы
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetCustomerIdpInvalid(): array
    {
        return [
            // Булево значение true
            'bool true' => [true],
            // Булево значение false
            'bool false' => [false],
            // Число с плавающей точкой
            'float' => [12.34],
            // Пустой массив
            'empty array' => [[]],
            // Массив со значением
            'array with value' => [['123']],
            // Объект
            'object' => [new \stdClass()],
            // Пустая строка
            'empty string' => [''],
            // Строка из пробела
            'space only' => [' '],
            // Табуляция
            'tab only' => ["\t"],
            // Перевод строки
            'newline only' => ["\n"],
            // Пробел в начале строки
            'leading space' => [' customer-123'],
            // Пробел в конце строки
            'trailing space' => ['customer-123 '],
            // Пробелы по краям строки
            'leading and trailing spaces' => [' customer-123 '],
            // Превышение максимальной длины (65 > 64)
            'too long' => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setCustomerIdp() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetCustomerIdpInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::setCustomerIdp
     */
    public function testSetCustomerIdpInvalid($val)
    {
        $object = new class {
            use HasCustomerIdp;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setCustomerIdp($val);
    }

    /**
     * Валидные значения для Customer_IDP:
     * - строки
     * - целые числа
     * - кириллица
     * - строки длиной до 64 символов
     */
    public static function dataProviderSetCustomerIdpValid(): array
    {
        return [
            // Строка с числовым значением
            'numeric string' => ['123', '123'],
            // Целое число (приводится к строке)
            'integer' => [123, '123'],
            // Строка с дефисом
            'with dash' => ['customer-123', 'customer-123'],
            // Email
            'email' => ['user@example.com', 'user@example.com'],
            // Телефон
            'phone' => ['+79991234567', '+79991234567'],
            // Кириллица
            'cyrillic' => ['логин', 'логин'],
            // Граничное значение: ровно 64 символа
            'max length 64' => [str_repeat('a', 64), str_repeat('a', 64)],
        ];
    }

    /**
     * Проверяет, что метод setCustomerIdp() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetCustomerIdpValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::setCustomerIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::hasCustomerIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCustomerIdp::getCustomerIdp
     */
    public function testSetCustomerIdpValid($input, string $expected)
    {
        $object = new class {
            use HasCustomerIdp;
        };

        $object->setCustomerIdp($input);

        $this->assertTrue($object->hasCustomerIdp());
        $this->assertSame($expected, $object->getCustomerIdp());
    }
}
