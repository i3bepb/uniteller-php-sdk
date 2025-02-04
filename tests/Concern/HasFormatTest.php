<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasFormat;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasFormat
 */
class HasFormatTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию установлен формат CSV
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasFormat::getFormat
     */
    public function testGetFormatReturnsCsvByDefault()
    {
        $object = new class {
            use HasFormat;
        };

        $this->assertSame(Format::CSV, $object->getFormat());
    }

    /**
     * Проверяет, что метод setFormat() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasFormat::setFormat
     */
    public function testSetFormatReturnsSelf()
    {
        $object = new class {
            use HasFormat;
        };
        $result = $object->setFormat(Format::XML);

        $this->assertSame($object, $result);
    }

    /**
     * Невалидные значения для Format:
     * - пустые строки
     * - строки только из пробелов
     * - значения, не входящие в enum Format
     * - неверный регистр
     */
    public static function dataProviderSetFormatInvalid(): array
    {
        return [
            'empty string' => [''],
            'space only'   => [' '],
            'yaml'         => ['yaml'],
            'html'         => ['html'],
            'upper case'   => ['CSV'],
            'mixed case'   => ['Xml'],
        ];
    }

    /**
     * Проверяет, что метод setFormat() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetFormatInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasFormat::setFormat
     */
    public function testSetFormatInvalid($val)
    {
        $object = new class {
            use HasFormat;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setFormat($val);
    }

    /**
     * Валидные значения для Format:
     * - все допустимые значения из enum Format
     */
    public static function dataProviderSetFormatValid(): array
    {
        return [
            'csv'      => [Format::CSV],
            'wddx'     => [Format::WDDX],
            'brackets' => [Format::BRACKETS],
            'xml'      => [Format::XML],
            'soap'     => [Format::SOAP],
            'json'     => [Format::JSON],
        ];
    }

    /**
     * Проверяет, что метод setFormat() корректно принимает валидные значения и getFormat()
     * возвращает то же значение, которое было передано
     *
     * @dataProvider dataProviderSetFormatValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasFormat::setFormat
     * @covers \Tmconsulting\Uniteller\Concern\HasFormat::getFormat
     */
    public function testSetFormatValid($val)
    {
        $object = new class {
            use HasFormat;
        };
        $object->setFormat($val);

        $this->assertSame($val, $object->getFormat());
    }
}
