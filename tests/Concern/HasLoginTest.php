<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasLogin;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasLogin
 */
class HasLoginTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле Login не установлено
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLogin::hasLogin
     * @covers \Tmconsulting\Uniteller\Concern\HasLogin::getLogin
     */
    public function testHasLoginReturnsFalseByDefault()
    {
        $object = new class {
            use HasLogin;
        };

        $this->assertFalse($object->hasLogin());
        $this->assertSame('', $object->getLogin());
    }

    /**
     * Проверяет, что метод setLogin() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLogin::setLogin
     */
    public function testSetLoginReturnsSelf()
    {
        $object = new class {
            use HasLogin;
        };
        $result = $object->setLogin('login');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setLogin() корректно устанавливает значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLogin::hasLogin
     * @covers \Tmconsulting\Uniteller\Concern\HasLogin::getLogin
     */
    public function testSetLoginSetsValue()
    {
        $object = new class {
            use HasLogin;
        };
        $object->setLogin('my_login');

        $this->assertTrue($object->hasLogin());
        $this->assertSame('my_login', $object->getLogin());
    }

    /**
     * Невалидные значения для Login:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     */
    public static function dataProviderSetLoginInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'leading and trailing spaces' => [' login '],
        ];
    }

    /**
     * Проверяет, что метод setLogin() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetLoginInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLogin::setLogin
     */
    public function testSetLoginInvalid(string $val)
    {
        $object = new class {
            use HasLogin;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setLogin($val);
    }

    /**
     * Валидные значения для Login:
     * - обычные строки
     * - строки с цифрами
     * - строки со спецсимволами
     */
    public static function dataProviderSetLoginValid(): array
    {
        return [
            'simple string'           => ['login', 'login'],
            'with digits'             => ['login123', 'login123'],
            'with special characters' => ['login!-_1', 'login!-_1'],
        ];
    }

    /**
     * Проверяет, что метод setLogin() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetLoginValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLogin::hasLogin
     * @covers \Tmconsulting\Uniteller\Concern\HasLogin::getLogin
     */
    public function testSetLoginValid(string $val, string $expected)
    {
        $object = new class {
            use HasLogin;
        };
        $object->setLogin($val);

        $this->assertTrue($object->hasLogin());
        $this->assertSame($expected, $object->getLogin());
    }
}
