<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasOrderId;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasOrderId
 */
class HasOrderIdTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле Order_IDP не установлено
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderId::hasOrderId
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderId::getOrderId
     */
    public function testHasOrderIdReturnsFalseByDefault()
    {
        $object = new class {
            use HasOrderId;
        };

        $this->assertFalse($object->hasOrderId());
        $this->assertNull($object->getOrderId());
    }

    /**
     * Проверяет, что метод setOrderId() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderId::setOrderId
     */
    public function testSetOrderIdReturnsSelf()
    {
        $object = new class {
            use HasOrderId;
        };
        $result = $object->setOrderId('ORDER-123');

        $this->assertSame($object, $result);
    }

    /**
     * Невалидные значения для Order_IDP:
     * - неподдерживаемые типы
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - значения длиннее 127 символов
     */
    public static function dataProviderSetOrderIdInvalid(): array
    {
        return [
            'null'                        => [null],
            'bool true'                   => [true],
            'bool false'                  => [false],
            'float'                       => [12.34],
            'empty array'                 => [[]],
            'array with value'            => [['123']],
            'object'                      => [new \stdClass()],
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'too long'                    => [str_repeat('a', 128)],
            'leading and trailing spaces' => ['  ORDER-123  '],
        ];
    }

    /**
     * Проверяет, что метод setOrderId() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetOrderIdInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderId::setOrderId
     */
    public function testSetOrderIdInvalid($val)
    {
        $object = new class {
            use HasOrderId;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setOrderId($val);
    }

    /**
     * Валидные значения для Order_IDP:
     * - строки
     * - целые числа
     * - строки длиной до 127 символов
     */
    public static function dataProviderSetOrderIdValid(): array
    {
        return [
            'numeric string' => ['123', '123'],
            'integer'        => [123, '123'],
            'with dash'      => ['ORDER-123', 'ORDER-123'],
            'zero string'    => ['0', '0'],
            'zero integer'   => [0, '0'],
            'max length 127' => [str_repeat('a', 127), str_repeat('a', 127)],
        ];
    }

    /**
     * Проверяет, что метод setOrderId() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetOrderIdValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderId::hasOrderId
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderId::getOrderId
     * @covers \Tmconsulting\Uniteller\Concern\HasOrderId::setOrderId
     */
    public function testSetOrderIdValid($input, string $expected)
    {
        $object = new class {
            use HasOrderId;
        };
        $object->setOrderId($input);

        $this->assertTrue($object->hasOrderId());
        $this->assertSame($expected, $object->getOrderId());
    }
}
