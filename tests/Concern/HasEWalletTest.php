<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasEWallet;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasEWallet
 */
class HasEWalletTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию EWallet не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEWallet::hasEWallet
     * @covers \Tmconsulting\Uniteller\Concern\HasEWallet::getEWallet
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasEWallet;
        };

        $this->assertFalse($object->hasEWallet());
        $this->assertNull($object->getEWallet());
    }

    /**
     * Проверяет, что метод setEWallet() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEWallet::setEWallet
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasEWallet;
        };

        $this->assertSame($object, $object->setEWallet('wallet123'));
    }

    /**
     * Валидные значения для EWallet:
     * - null
     * - латиница
     * - цифры
     * - латиница + цифры
     * - строка длиной 64 символа
     */
    public static function dataProviderSetEWalletValid(): array
    {
        return [
            'null'          => [null, null, false],
            'letters'       => ['ABCDEF', 'ABCDEF', true],
            'digits'        => ['123456', '123456', true],
            'alphanumeric'  => ['ABC123xyz', 'ABC123xyz', true],
            'max length 64' => [str_repeat('a', 64), str_repeat('a', 64), true],
        ];
    }

    /**
     * Проверяет, что метод setEWallet() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetEWalletValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEWallet::setEWallet
     * @covers \Tmconsulting\Uniteller\Concern\HasEWallet::hasEWallet
     * @covers \Tmconsulting\Uniteller\Concern\HasEWallet::getEWallet
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasEWallet
     */
    public function testSetEWalletValid($value, $expected, $hasEWallet)
    {
        $object = new class {
            use HasEWallet;
        };

        $object->setEWallet($value);

        $this->assertSame($hasEWallet, $object->hasEWallet());
        $this->assertSame($expected, $object->getEWallet());
    }

    /**
     * Невалидные значения для EWallet:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - кириллица
     * - спецсимволы
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetEWalletInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' ABC123'],
            'trailing space'              => ['ABC123 '],
            'leading and trailing spaces' => [' ABC123 '],
            'cyrillic'                    => ['Кошелек123'],
            'with dash'                   => ['ABC-123'],
            'with underscore'             => ['ABC_123'],
            'with special chars'          => ['ABC@123'],
            'too long'                    => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setEWallet() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetEWalletInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEWallet::setEWallet
     *
     * @param string $value
     */
    public function testSetEWalletNotValid($value)
    {
        $object = new class {
            use HasEWallet;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setEWallet($value);
    }
}
