<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasBackUrl;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl
 */
class HasBackUrlTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию BackUrl не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::hasBackUrl
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::getBackUrl
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasBackUrl;
        };

        $this->assertFalse($object->hasBackUrl());
        $this->assertNull($object->getBackUrl());
    }

    /**
     * Проверяет, что метод setBackUrl() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::setBackUrl
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasBackUrl;
        };

        $this->assertSame($object, $object->setBackUrl('https://example.com/return'));
    }

    /**
     * Валидные значения для BackUrl:
     * - корректные HTTPS URL
     * - URL с query-параметрами
     * - URL с fragment
     * - домены в punycode
     * - граничное значение длины
     *
     * @return array
     */
    public function backUrlProviderValid(): array
    {
        return [
            // Простой HTTPS URL
            'https url' => ['https://example.com/return'],
            // URL с query-параметрами
            'with query' => ['https://example.com/return?order=123&status=ok'],
            // URL с fragment
            'with fragment' => ['https://example.com/return#result'],
            // URL в punycode допустим
            'punycode domain' => ['https://xn--d1acpjx3f.xn--p1ai/return'],
            // Граничное значение: ровно 255 символов
            'max length 255' => ['https://example.com/' . str_repeat('a', 235)],
        ];
    }

    /**
     * Проверяет, что метод setBackUrl() корректно принимает валидные значения.
     *
     * @dataProvider backUrlProviderValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::setBackUrl
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::hasBackUrl
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::getBackUrl
     */
    public function testSetValidBackUrl(string $value)
    {
        $object = new class {
            use HasBackUrl;
        };

        $object->setBackUrl($value);

        $this->assertTrue($object->hasBackUrl());
        $this->assertSame($value, $object->getBackUrl());
    }

    /**
     * Проверяет, что значение можно сбросить через null.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::setBackUrl
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::hasBackUrl
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::getBackUrl
     */
    public function testSetNullResetsValue()
    {
        $object = new class {
            use HasBackUrl;
        };

        $object->setBackUrl('https://example.com/return');
        $this->assertTrue($object->hasBackUrl());
        $this->assertSame('https://example.com/return', $object->getBackUrl());

        $object->setBackUrl(null);

        $this->assertFalse($object->hasBackUrl());
        $this->assertNull($object->getBackUrl());
    }

    /**
     * Невалидные значения для BackUrl:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - URL с кириллицей (требуется punycode)
     * - строки длиннее 255 символов
     *
     * @return array
     */
    public function backUrlProviderInvalid(): array
    {
        return [
            // Пустая строка
            'empty string' => [''],
            // Строка состоит только из пробелов
            'spaces only' => ['   '],
            // Пробел в начале строки
            'leading space' => [' https://example.com/return'],
            // Пробел в конце строки
            'trailing space' => ['https://example.com/return '],
            // Кириллица в URL (нужно использовать punycode)
            'cyrillic domain' => ['https://пример.рф/return'],
            // Превышение максимальной длины (256 > 255)
            'too long' => ['https://example.com/' . str_repeat('a', 236)],
        ];
    }

    /**
     * Проверяет невалидные значения BackUrl.
     *
     * @dataProvider backUrlProviderInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBackUrl::setBackUrl
     */
    public function testSetInvalidBackUrl(string $value)
    {
        $object = new class {
            use HasBackUrl;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setBackUrl($value);
    }
}
