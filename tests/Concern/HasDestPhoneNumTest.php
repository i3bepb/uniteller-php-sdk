<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasDestPhoneNum;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasDestPhoneNum
 */
class HasDestPhoneNumTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию DestPhoneNum не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasDestPhoneNum::hasDestPhoneNum
     * @covers \Tmconsulting\Uniteller\Concern\HasDestPhoneNum::getDestPhoneNum
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasDestPhoneNum;
        };

        $this->assertFalse($object->hasDestPhoneNum());
        $this->assertNull($object->getDestPhoneNum());
    }

    /**
     * Проверяет, что метод setDestPhoneNum() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasDestPhoneNum::setDestPhoneNum
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasDestPhoneNum;
        };

        $this->assertSame($object, $object->setDestPhoneNum('+79000000000'));
    }

    /**
     * Валидные значения для DestPhoneNum:
     * - null
     * - корректный номер в формате +7XXXXXXXXXX
     */
    public static function dataProviderSetDestPhoneNumValid(): array
    {
        return [
            'null' => [null, null, false],
            'valid phone 1' => ['+79000000000', '+79000000000', true],
            'valid phone 2' => ['+79991234567', '+79991234567', true],
            'valid phone 3' => ['+71111111111', '+71111111111', true],
        ];
    }

    /**
     * Проверяет, что метод setDestPhoneNum() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetDestPhoneNumValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasDestPhoneNum::setDestPhoneNum
     * @covers \Tmconsulting\Uniteller\Concern\HasDestPhoneNum::hasDestPhoneNum
     * @covers \Tmconsulting\Uniteller\Concern\HasDestPhoneNum::getDestPhoneNum
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool        $hasDestPhoneNum
     */
    public function testSetDestPhoneNumValid($value, $expected, $hasDestPhoneNum)
    {
        $object = new class {
            use HasDestPhoneNum;
        };

        $object->setDestPhoneNum($value);

        $this->assertSame($hasDestPhoneNum, $object->hasDestPhoneNum());
        $this->assertSame($expected, $object->getDestPhoneNum());
    }

    /**
     * Невалидные значения для DestPhoneNum:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - неверный формат
     * - неверная длина
     * - другой код страны
     * - наличие букв или лишних символов
     */
    public static function dataProviderSetDestPhoneNumInvalid(): array
    {
        return [
            'empty string' => [''],
            'space only' => [' '],
            'tab only' => ["\t"],
            'newline only' => ["\n"],
            'leading space' => [' +79000000000'],
            'trailing space' => ['+79000000000 '],
            'leading and trailing spaces' => [' +79000000000 '],
            'without plus' => ['79000000000'],
            'wrong country code 8' => ['89000000000'],
            'wrong country code 9' => ['+99000000000'],
            'too short' => ['+7900000000'],
            'too long' => ['+790000000000'],
            'contains letters' => ['+79000000abc'],
            'contains dash' => ['+7-9000000000'],
            'contains spaces inside' => ['+7 9000000000'],
            'plus only' => ['+'],
        ];
    }

    /**
     * Проверяет, что метод setDestPhoneNum() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetDestPhoneNumInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasDestPhoneNum::setDestPhoneNum
     *
     * @param string $value
     */
    public function testSetDestPhoneNumInvalid($value)
    {
        $object = new class {
            use HasDestPhoneNum;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setDestPhoneNum($value);
    }
}
