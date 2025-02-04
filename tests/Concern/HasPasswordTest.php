<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasPassword;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasPassword
 */
class HasPasswordTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле Password не установлено
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPassword::hasPassword
     * @covers \Tmconsulting\Uniteller\Concern\HasPassword::getPassword
     */
    public function testHasPasswordReturnsFalseByDefault()
    {
        $object = new class {
            use HasPassword;
        };

        $this->assertFalse($object->hasPassword());
        $this->assertSame('', $object->getPassword());
    }

    /**
     * Проверяет, что метод setPassword() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPassword::setPassword
     */
    public function testSetPasswordReturnsSelf()
    {
        $object = new class {
            use HasPassword;
        };
        $result = $object->setPassword('secret');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setPassword() корректно устанавливает значение:
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPassword::hasPassword
     * @covers \Tmconsulting\Uniteller\Concern\HasPassword::getPassword
     */
    public function testSetPasswordSetsValue()
    {
        $object = new class {
            use HasPassword;
        };
        $object->setPassword('secret');

        $this->assertTrue($object->hasPassword());
        $this->assertSame('secret', $object->getPassword());
    }

    /**
     * Невалидные значения для Password:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     */
    public static function dataProviderSetPasswordInvalid(): array
    {
        return [
            'empty string'                => [''],
            'spaces only'                 => ['  '],
            'leading and trailing spaces' => [' pa$$w0rd '],
        ];
    }

    /**
     * Проверяет, что метод setPassword() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetPasswordInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPassword::setPassword
     */
    public function testSetPasswordInvalid(string $val)
    {
        $object = new class {
            use HasPassword;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setPassword($val);
    }

    /**
     * Валидные значения для Password:
     * - обычные строки
     * - строки с цифрами и спецсимволами
     */
    public static function dataProviderSetPasswordValid(): array
    {
        return [
            'simple string'           => ['secret', 'secret'],
            'with digits'             => ['password123', 'password123'],
            'with special characters' => ['pa$$w0rd', 'pa$$w0rd'],
        ];
    }

    /**
     * Проверяет, что метод setPassword() корректно принимает валидные значения:
     *
     * @dataProvider dataProviderSetPasswordValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPassword::hasPassword
     * @covers \Tmconsulting\Uniteller\Concern\HasPassword::getPassword
     */
    public function testSetPasswordValid(string $val, string $expected)
    {
        $object = new class {
            use HasPassword;
        };
        $object->setPassword($val);

        $this->assertTrue($object->hasPassword());
        $this->assertSame($expected, $object->getPassword());
    }
}
