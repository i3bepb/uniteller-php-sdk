<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasEmail;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasEmail
 */
class HasEmailTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию email не установлен
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEmail::hasEmail
     * @covers \Tmconsulting\Uniteller\Concern\HasEmail::getEmail
     */
    public function testHasEmailReturnsFalseByDefault()
    {
        $object = new class {
            use HasEmail;
        };

        $this->assertFalse($object->hasEmail());
        $this->assertNull($object->getEmail());
    }

    /**
     * Проверяет, что метод setEmail() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEmail::setEmail
     */
    public function testSetEmailReturnsSelf()
    {
        $object = new class {
            use HasEmail;
        };

        $result = $object->setEmail('test@example.com');

        $this->assertSame($object, $result);
    }

    /**
     * Невалидные значения для Email:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - невалидный формат email
     * - строки с кириллицей
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetEmailInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' test@example.com'],
            'trailing space'              => ['test@example.com '],
            'leading and trailing spaces' => [' test@example.com '],
            'no domain'                   => ['test'],
            'no domain after at'          => ['test@'],
            'no local part'               => ['@example.com'],
            'no tld'                      => ['test@example'],
            'invalid tld format'          => ['test@.com'],
            'double at'                   => ['test@@example.com'],
            'cyrillic local part'         => ['тест@example.com'],
            'cyrillic domain'             => ['test@тест.рф'],
            'too long'                    => [str_repeat('a', 53) . '@example.com'],
        ];
    }

    /**
     * Проверяет, что метод setEmail() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetEmailInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEmail::setEmail
     */
    public function testSetEmailInvalid(string $val)
    {
        $object = new class {
            use HasEmail;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setEmail($val);
    }

    /**
     * Валидные значения для Email:
     * - стандартные email
     * - email с точками и спецсимволами
     * - email с поддоменами и дефисами
     * - email с punycode-доменом
     * - строки длиной до 64 символов
     */
    public static function dataProviderSetEmailValid(): array
    {
        $email64 = str_repeat('a', 52) . '@example.com';

        return [
            'simple email'        => ['test@example.com', 'test@example.com'],
            'dot in local part'   => ['user.name@example.com', 'user.name@example.com'],
            'plus tag'            => ['user+tag@example.com', 'user+tag@example.com'],
            'underscore and dash' => ['user_name@example-domain.com', 'user_name@example-domain.com'],
            'punycode domain'     => ['test@xn--e1aybc.xn--p1ai', 'test@xn--e1aybc.xn--p1ai'],
            'max length 64'       => [$email64, $email64],
        ];
    }

    /**
     * Проверяет, что метод setEmail() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetEmailValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEmail::setEmail
     * @covers \Tmconsulting\Uniteller\Concern\HasEmail::getEmail
     * @covers \Tmconsulting\Uniteller\Concern\HasEmail::hasEmail
     */
    public function testSetEmailValid(string $input, string $expected)
    {
        $object = new class {
            use HasEmail;
        };

        $object->setEmail($input);

        $this->assertTrue($object->hasEmail());
        $this->assertSame($expected, $object->getEmail());
    }
}
