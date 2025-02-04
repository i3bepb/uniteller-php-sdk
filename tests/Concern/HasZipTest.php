<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasZip;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasZip
 */
class HasZipTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию Zip не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasZip::hasZip
     * @covers \Tmconsulting\Uniteller\Concern\HasZip::getZip
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasZip;
        };

        $this->assertFalse($object->hasZip());
        $this->assertNull($object->getZip());
    }

    /**
     * Проверяет, что метод setZip() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasZip::setZip
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasZip;
        };

        $this->assertSame($object, $object->setZip('101000'));
    }

    /**
     * Валидные значения для Zip:
     * - null
     * - строка с цифрами
     * - int
     * - буквенно-цифровая строка
     * - строка длиной ровно 64 символа
     */
    public static function dataProviderSetZipValid(): array
    {
        return [
            'null'          => [null, null, false],
            'digits string' => ['101000', '101000', true],
            'int'           => [101000, '101000', true],
            'zero int'      => [0, '0', true],
            'alphanumeric'  => ['LV-1050', 'LV-1050', true],
            'max length 64' => [str_repeat('a', 64), str_repeat('a', 64), true],
        ];
    }

    /**
     * Проверяет, что метод setZip() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetZipValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasZip::setZip
     * @covers \Tmconsulting\Uniteller\Concern\HasZip::hasZip
     * @covers \Tmconsulting\Uniteller\Concern\HasZip::getZip
     *
     * @param string|int|null $value
     * @param string|null $expected
     * @param bool $hasZip
     */
    public function testSetZipValid($value, $expected, $hasZip)
    {
        $object = new class {
            use HasZip;
        };

        $object->setZip($value);

        $this->assertSame($hasZip, $object->hasZip());
        $this->assertSame($expected, $object->getZip());
    }

    /**
     * Невалидные значения для Zip:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetZipInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' 101000'],
            'trailing space'              => ['101000 '],
            'leading and trailing spaces' => [' 101000 '],
            'too long'                    => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setZip() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetZipInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasZip::setZip
     *
     * @param string $value
     */
    public function testSetZipNotValid($value)
    {
        $object = new class {
            use HasZip;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setZip($value);
    }

    /**
     * Невалидные типы для Zip:
     * - массив
     * - float
     * - bool
     * - object
     */
    public static function dataProviderSetZipInvalidType(): array
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
     * Проверяет, что метод setZip() выбрасывает исключение при передаче значения неверного типа.
     *
     * @dataProvider dataProviderSetZipInvalidType
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasZip::setZip
     *
     * @param mixed $value
     */
    public function testSetZipInvalidType($value)
    {
        $object = new class {
            use HasZip;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setZip($value);
    }
}
