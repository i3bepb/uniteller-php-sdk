<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasAddress;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasAddress
 */
class HasAddressTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле Address не установлено.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::hasAddress
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::getAddress
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasAddress;
        };
        $this->assertFalse($object->hasAddress());
        $this->assertNull($object->getAddress());
    }

    /**
     * Проверяет, что метод setAddress() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::setAddress
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasAddress;
        };
        $this->assertSame($object, $object->setAddress('Street1'));
    }

    /**
     * Валидные значения для Address:
     * - простые строки
     * - кириллица
     * - строки с пробелами и пунктуацией
     * - граничное значение длины
     *
     * @return array
     */
    public function addressProviderValid(): array
    {
        return [
            // Простая латиница без спецсимволов
            'simple latin' => ['Street1'],
            // Кириллица (проверка работы mb_strlen и UTF-8)
            'cyrillic' => ['с.Патруши'],
            // Адрес с пробелами и пунктуацией
            'with punctuation' => ['ул. Ленина, 1'],
            // Граничное значение: ровно 128 символов
            'max length 128' => [str_repeat('а', 128)],
        ];
    }

    /**
     * Проверяет, что метод setAddress() корректно принимает валидные значения.
     *
     * @dataProvider addressProviderValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::setAddress
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::hasAddress
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::getAddress
     */
    public function testSetValidAddress(string $value)
    {
        $object = new class {
            use HasAddress;
        };
        $object->setAddress($value);
        $this->assertTrue($object->hasAddress());
        $this->assertSame($value, $object->getAddress());
    }

    /**
     * Проверяет, что значение можно сбросить через null.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::setAddress
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::hasAddress
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::getAddress
     */
    public function testSetNullResetsValue()
    {
        $object = new class {
            use HasAddress;
        };

        $object->setAddress('ул. Ленина, 1');
        $this->assertTrue($object->hasAddress());
        $this->assertSame('ул. Ленина, 1', $object->getAddress());

        $object->setAddress(null);
        $this->assertFalse($object->hasAddress());
        $this->assertNull($object->getAddress());
    }

    /**
     * Невалидные значения для Address:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 128 символов
     *
     * @return array
     */
    public function addressProviderInvalid(): array
    {
        return [
            // Пустая строка
            'empty string' => [''],
            // Строка состоит только из пробелов
            'spaces only' => ['   '],
            // Пробел в начале строки
            'leading space' => [' Street'],
            // Пробел в конце строки
            'trailing space' => ['Street '],
            // Превышение максимальной длины (129 > 128)
            'too long' => [str_repeat('а', 129)],
        ];
    }

    /**
     * Проверяет невалидные значения Address.
     *
     * @dataProvider addressProviderInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasAddress::setAddress
     */
    public function testSetInvalidAddress(string $value)
    {
        $object = new class {
            use HasAddress;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setAddress($value);
    }
}
