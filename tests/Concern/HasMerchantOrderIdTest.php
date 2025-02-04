<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasMerchantOrderId;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId
 */
class HasMerchantOrderIdTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию MerchantOrderId не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId::hasMerchantOrderId
     * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId::getMerchantOrderId
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasMerchantOrderId;
        };

        $this->assertFalse($object->hasMerchantOrderId());
        $this->assertNull($object->getMerchantOrderId());
    }

    /**
     * Проверяет, что метод setMerchantOrderId() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId::setMerchantOrderId
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasMerchantOrderId;
        };

        $this->assertSame($object, $object->setMerchantOrderId('ORDER-123'));
    }

    /**
     * Валидные значения для MerchantOrderId:
     * - null
     * - строка
     * - число
     * - строка на кириллице
     * - строка длиной ровно 256 символов
     */
    public static function dataProviderSetMerchantOrderIdValid(): array
    {
        return [
            'null'            => [null, null, false],
            'string'          => ['ORDER-123', 'ORDER-123', true],
            'int'             => [12345, '12345', true],
            'zero int'        => [0, '0', true],
            'cyrillic string' => ['Заказ-123', 'Заказ-123', true],
            'max length 256'  => [str_repeat('a', 256), str_repeat('a', 256), true],
        ];
    }

    /**
     * Проверяет, что метод setMerchantOrderId() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetMerchantOrderIdValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId::setMerchantOrderId
     * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId::hasMerchantOrderId
     * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId::getMerchantOrderId
     *
     * @param string|int|null $value
     * @param string|null $expected
     * @param bool $hasMerchantOrderId
     */
    public function testSetMerchantOrderIdValid($value, $expected, $hasMerchantOrderId)
    {
        $object = new class {
            use HasMerchantOrderId;
        };

        $object->setMerchantOrderId($value);

        $this->assertSame($hasMerchantOrderId, $object->hasMerchantOrderId());
        $this->assertSame($expected, $object->getMerchantOrderId());
    }

    /**
     * Невалидные значения для MerchantOrderId:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки с запрещёнными символами ";" и "="
     * - строки длиннее 256 символов
     */
    public static function dataProviderSetMerchantOrderIdInvalid(): array
    {
        return [
            'empty string'                  => [''],
            'space only'                    => [' '],
            'tab only'                      => ["\t"],
            'newline only'                  => ["\n"],
            'leading space'                 => [' ORDER-123'],
            'trailing space'                => ['ORDER-123 '],
            'leading and trailing spaces'   => [' ORDER-123 '],
            'contains semicolon'            => ['ORDER;123'],
            'contains equal sign'           => ['ORDER=123'],
            'contains both forbidden chars' => ['ORDER;=123'],
            'too long'                      => [str_repeat('a', 257)],
        ];
    }

    /**
     * Проверяет, что метод setMerchantOrderId() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetMerchantOrderIdInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId::setMerchantOrderId
     *
     * @param string $value
     */
    public function testSetMerchantOrderIdNotValid($value)
    {
        $object = new class {
            use HasMerchantOrderId;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setMerchantOrderId($value);
    }

    /**
     * Невалидные типы для MerchantOrderId:
     * - массив
     * - float
     * - bool
     * - object
     */
    public static function dataProviderSetMerchantOrderIdInvalidType(): array
    {
        return [
            'array'  => [[]],
            'float'  => [12.34],
            'true'   => [true],
            'false'  => [false],
            'object' => [new \stdClass()],
        ];
    }

    /**
     * Проверяет, что метод setMerchantOrderId() выбрасывает исключение при передаче значения неверного типа.
     *
     * @dataProvider dataProviderSetMerchantOrderIdInvalidType
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMerchantOrderId::setMerchantOrderId
     *
     * @param mixed $value
     */
    public function testSetMerchantOrderIdInvalidType($value)
    {
        $object = new class {
            use HasMerchantOrderId;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setMerchantOrderId($value);
    }
}
