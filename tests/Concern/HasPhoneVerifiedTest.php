<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasPhoneVerified;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasPhoneVerified
 */
class HasPhoneVerifiedTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию PhoneVerified не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPhoneVerified::hasPhoneVerified
     * @covers \Tmconsulting\Uniteller\Concern\HasPhoneVerified::getPhoneVerified
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasPhoneVerified;
        };

        $this->assertFalse($object->hasPhoneVerified());
        $this->assertNull($object->getPhoneVerified());
    }

    /**
     * Проверяет, что метод setPhoneVerified() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPhoneVerified::setPhoneVerified
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasPhoneVerified;
        };

        $this->assertSame($object, $object->setPhoneVerified('+79991234567'));
    }

    /**
     * Валидные значения для PhoneVerified:
     * - null
     * - номер в международном формате
     * - номер в локальном формате
     * - строка с пробелами внутри
     * - строка длиной ровно 64 символа
     */
    public static function dataProviderSetPhoneVerifiedValid(): array
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
     * Проверяет, что метод setPhoneVerified() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetPhoneVerifiedValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPhoneVerified::setPhoneVerified
     * @covers \Tmconsulting\Uniteller\Concern\HasPhoneVerified::hasPhoneVerified
     * @covers \Tmconsulting\Uniteller\Concern\HasPhoneVerified::getPhoneVerified
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasPhoneVerified
     */
    public function testSetPhoneVerifiedValid($value, $expected, $hasPhoneVerified)
    {
        $object = new class {
            use HasPhoneVerified;
        };

        $object->setPhoneVerified($value);

        $this->assertSame($hasPhoneVerified, $object->hasPhoneVerified());
        $this->assertSame($expected, $object->getPhoneVerified());
    }

    /**
     * Невалидные значения для PhoneVerified:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetPhoneVerifiedInvalid(): array
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
     * Проверяет, что метод setPhoneVerified() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetPhoneVerifiedInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPhoneVerified::setPhoneVerified
     *
     * @param string $value
     */
    public function testSetPhoneVerifiedNotValid($value)
    {
        $object = new class {
            use HasPhoneVerified;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setPhoneVerified($value);
    }
}
