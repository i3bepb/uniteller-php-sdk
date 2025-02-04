<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasDeepLink;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasDeepLink
 */
class HasDeepLinkTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию DeepLink не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasDeepLink::hasDeepLink
     * @covers \Tmconsulting\Uniteller\Concern\HasDeepLink::getDeepLink
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasDeepLink;
        };

        $this->assertFalse($object->hasDeepLink());
        $this->assertNull($object->getDeepLink());
    }

    /**
     * Проверяет, что метод setDeepLink() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasDeepLink::setDeepLink
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasDeepLink;
        };

        $this->assertSame($object, $object->setDeepLink('https://myapp.ru/payment-done'));
    }

    /**
     * Валидные значения для DeepLink:
     * - null
     * - обычный deep link
     * - https url
     * - строка длиной ровно 255 символов
     */
    public static function dataProviderSetDeepLinkValid(): array
    {
        return [
            'null'           => [null, null, false],
            'deep link'      => ['myapp://payment/return', 'myapp://payment/return', true],
            'https url'      => ['https://example.com/return', 'https://example.com/return', true],
            'max length 255' => [str_repeat('a', 255), str_repeat('a', 255), true],
        ];
    }

    /**
     * @dataProvider dataProviderSetDeepLinkValid
     *
     * @covers       \Tmconsulting\Uniteller\Concern\HasDeepLink::setDeepLink
     * @covers       \Tmconsulting\Uniteller\Concern\HasDeepLink::hasDeepLink
     * @covers       \Tmconsulting\Uniteller\Concern\HasDeepLink::getDeepLink
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasDeepLink
     */
    public function testSetDeepLinkValid($value, $expected, $hasDeepLink)
    {
        $object = new class {
            use HasDeepLink;
        };

        $object->setDeepLink($value);

        $this->assertSame($hasDeepLink, $object->hasDeepLink());
        $this->assertSame($expected, $object->getDeepLink());
    }

    /**
     * Невалидные значения для DeepLink:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 255 символов
     */
    public static function dataProviderSetDeepLinkInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' myapp://payment/return'],
            'trailing space'              => ['myapp://payment/return '],
            'leading and trailing spaces' => [' myapp://payment/return '],
            'too long'                    => [str_repeat('a', 256)],
        ];
    }

    /**
     * @dataProvider dataProviderSetDeepLinkInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasDeepLink::setDeepLink
     *
     * @param string $value
     */
    public function testSetDeepLinkInvalid($value)
    {
        $object = new class {
            use HasDeepLink;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setDeepLink($value);
    }
}
