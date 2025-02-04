<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasPhone;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasPhone
 */
class HasPhoneTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле Phone не установлено.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPhone::hasPhone
     * @covers \Tmconsulting\Uniteller\Concern\HasPhone::getPhone
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasPhone;
        };

        $this->assertFalse($object->hasPhone());
        $this->assertNull($object->getPhone());
    }

    /**
     * Проверяет, что метод setPhone() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPhone::setPhone
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasPhone;
        };

        $this->assertSame($object, $object->setPhone('+79000000000'));
    }

    /**
     * Валидные значения для Phone:
     * - null
     * - номер в международном формате
     * - номер в локальном формате
     * - строка с пробелами внутри
     * - строка длиной ровно 64 символа
     */
    public static function dataProviderSetPhoneValid(): array
    {
        return [
            'null'                 => [null, null, false],
            'international format' => ['+79991234567', '+79991234567', true],
            'local format'         => ['89991234567', '89991234567', true],
            'spaces inside'        => ['+7 999 123-45-67', '+7 999 123-45-67', true],
            'max length 64'        => [str_repeat('a', 64), str_repeat('a', 64), true],
        ];
    }

    /**
     * Проверяет, что метод setPhone() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetPhoneValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPhone::setPhone
     * @covers \Tmconsulting\Uniteller\Concern\HasPhone::hasPhone
     * @covers \Tmconsulting\Uniteller\Concern\HasPhone::getPhone
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasPhone
     */
    public function testSetPhoneValid($value, $expected, $hasPhone)
    {
        $object = new class {
            use HasPhone;
        };

        $object->setPhone($value);

        $this->assertSame($hasPhone, $object->hasPhone());
        $this->assertSame($expected, $object->getPhone());
    }

    /**
     * Невалидные значения для Phone:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetPhoneInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' +79991234567'],
            'trailing space'              => ['+79991234567 '],
            'leading and trailing spaces' => [' +79991234567 '],
            'too long'                    => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setPhone() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetPhoneInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPhone::setPhone
     *
     * @param string $value
     */
    public function testSetPhoneNotValid($value)
    {
        $object = new class {
            use HasPhone;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setPhone($value);
    }
}
