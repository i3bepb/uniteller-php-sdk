<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Builder\Enum\BaseUri;
use Tmconsulting\Uniteller\Concern\HasBaseUri;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasBaseUri
 */
class HasBaseUriTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию getBaseUri() возвращает значение BaseUri::DEFAULT
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBaseUri::getBaseUri
     */
    public function testGetBaseUriReturnsDefaultByDefault()
    {
        $object = new class {
            use HasBaseUri;
        };

        $this->assertSame(BaseUri::SIMPLE, $object->getBaseUri());
    }

    /**
     * Проверяет, что метод setBaseUri() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBaseUri::setBaseUri
     */
    public function testSetBaseUriReturnsSelf()
    {
        $object = new class {
            use HasBaseUri;
        };
        $result = $object->setBaseUri('https://test.uniteller.ru');

        $this->assertSame($object, $result);
    }

    /**
     * Валидные значения для поля baseUri:
     * - обычный https URL
     * - URL с http
     * - URL с префиксом пути
     * - URL с завершающим slash (нормализуется)
     * - URL с несколькими завершающими slash (нормализуется)
     * - URL со схемой в верхнем регистре
     */
    public static function dataProviderSetBaseUriValid(): array
    {
        return [
            // Обычный HTTPS URL
            'https url' => ['https://test.uniteller.ru', 'https://test.uniteller.ru'],
            // URL с HTTP
            'http url' => ['http://test.uniteller.ru', 'http://test.uniteller.ru'],
            // URL с префиксом пути
            'with path' => ['https://test.uniteller.ru/pay', 'https://test.uniteller.ru/pay'],
            // URL с одним завершающим slash (нормализуется)
            'trailing slash' => ['https://test.uniteller.ru/', 'https://test.uniteller.ru'],
            // URL с несколькими завершающими slash (нормализуется)
            'multiple trailing slashes' => ['https://test.uniteller.ru///', 'https://test.uniteller.ru'],
            // Схема в верхнем регистре
            'upper case scheme' => ['HTTPS://test.uniteller.ru', 'HTTPS://test.uniteller.ru'],
        ];
    }

    /**
     * Проверяет, что метод setBaseUri() корректно принимает валидные значения и getBaseUri() возвращает
     * нормализованное значение
     *
     * @dataProvider dataProviderSetBaseUriValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBaseUri::setBaseUri
     * @covers \Tmconsulting\Uniteller\Concern\HasBaseUri::getBaseUri
     */
    public function testSetBaseUriValid(string $input, string $expected)
    {
        $object = new class {
            use HasBaseUri;
        };
        $object->setBaseUri($input);

        $this->assertSame($expected, $object->getBaseUri());
    }

    /**
     * Невалидные значения для поля baseUri:
     * - пустые строки
     * - строки только из пробелов
     * - URL без host
     * - URL без scheme
     * - неподдерживаемая схема
     * - наличие query
     * - наличие fragment
     * - строки с пробелами по краям
     */
    public static function dataProviderSetBaseUriInvalid(): array
    {
        return [
            // Пустая строка
            'empty string' => [''],
            // Строка состоит только из пробелов
            'spaces only' => [' '],
            // URL без host
            'no host' => ['https://'],
            // URL без scheme
            'no scheme' => ['test.uniteller.ru'],
            // Неподдерживаемая схема
            'unsupported scheme' => ['ftp://test.uniteller.ru'],
            // Наличие query
            'with query' => ['https://test.uniteller.ru?test=1'],
            // Наличие fragment
            'with fragment' => ['https://test.uniteller.ru#fragment'],
            // Пробелы по краям строки
            'leading and trailing spaces' => ['  https://test.uniteller.ru  '],
        ];
    }

    /**
     * Проверяет, что метод setBaseUri() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetBaseUriInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasBaseUri::setBaseUri
     */
    public function testSetBaseUriInvalid(string $val)
    {
        $object = new class {
            use HasBaseUri;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setBaseUri($val);
    }
}
