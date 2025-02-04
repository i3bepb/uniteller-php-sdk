<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasLanguage;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\Language;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasLanguage
 */
class HasLanguageTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию language не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLanguage::getLanguage
     * @covers \Tmconsulting\Uniteller\Concern\HasLanguage::hasLanguage
     */
    public function testGetLanguageReturnsNullByDefault()
    {
        $object = new class {
            use HasLanguage;
        };

        $this->assertNull($object->getLanguage());
        $this->assertFalse($object->hasLanguage());
    }

    /**
     * Проверяет, что метод setLanguage() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLanguage::setLanguage
     */
    public function testSetLanguageReturnsSelf()
    {
        $object = new class {
            use HasLanguage;
        };

        $result = $object->setLanguage(Language::RU);

        $this->assertSame($object, $result);
    }

    /**
     * Невалидные значения для Language:
     * - пустые строки
     * - строки только из пробелов
     * - строки, не входящие в enum Language
     * - неверный регистр
     * - строки с пробелами по краям
     */
    public static function dataProviderSetLanguageInvalid(): array
    {
        return [
            'empty string'         => [''],
            'space only'           => [' '],
            'upper case RU'        => ['RU'],
            'upper case EN'        => ['EN'],
            'unsupported language' => ['de'],
            'trailing space'       => ['ru '],
            'leading space'        => [' en'],
        ];
    }

    /**
     * Проверяет, что метод setLanguage() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetLanguageInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLanguage::setLanguage
     */
    public function testSetLanguageInvalid(string $val)
    {
        $object = new class {
            use HasLanguage;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setLanguage($val);
    }

    /**
     * Валидные значения для Language:
     * - значения из enum Language
     */
    public static function dataProviderSetLanguageValid(): array
    {
        return [
            'ru' => [Language::RU, Language::RU],
            'en' => [Language::EN, Language::EN],
        ];
    }

    /**
     * Проверяет, что метод setLanguage() корректно принимает валидные значения.
     *
     * @dataProvider dataProviderSetLanguageValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLanguage::setLanguage
     * @covers \Tmconsulting\Uniteller\Concern\HasLanguage::getLanguage
     */
    public function testSetLanguageValid(string $input, string $expected)
    {
        $object = new class {
            use HasLanguage;
        };

        $object->setLanguage($input);

        $this->assertSame($expected, $object->getLanguage());
    }
}
