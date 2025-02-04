<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use Tmconsulting\Uniteller\Exception\FormatNotSupportedException;
use Tmconsulting\Uniteller\Exception\EndpointNotSupportedException;
use Tmconsulting\Uniteller\Exception\ParserNotImplementedException;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserCsv;
use Tmconsulting\Uniteller\Request\ParserJson;
use Tmconsulting\Uniteller\Request\ParserXml;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Request\Format
 */
class FormatTest extends TestCase
{
    public static function dataProviderResolve(): array
    {
        return [
            // Для endpoint-а card
            [Format::CSV, ApiEndpoints::CARD, 1],
            [Format::WDDX, ApiEndpoints::CARD, 2],
            [Format::XML, ApiEndpoints::CARD, 3],
            // Для endpoint-а confirm
            [Format::CSV, ApiEndpoints::CONFIRM, 1],
            [Format::WDDX, ApiEndpoints::CONFIRM, 2],
            [Format::XML, ApiEndpoints::CONFIRM, 3],
            // Для endpoint-а recurrent поддерживается только CSV
            [Format::CSV, ApiEndpoints::RECURRENT, 1],
            // Для endpoint-а cancel
            [Format::CSV, ApiEndpoints::CANCEL, 1],
            [Format::WDDX, ApiEndpoints::CANCEL, 2],
            [Format::XML, ApiEndpoints::CANCEL, 3],
            [Format::SOAP, ApiEndpoints::CANCEL, 4],
            // Для endpoint-а results
            [Format::CSV, ApiEndpoints::RESULTS, 1],
            [Format::WDDX, ApiEndpoints::RESULTS, 2],
            [Format::BRACKETS, ApiEndpoints::RESULTS, 3],
            [Format::XML, ApiEndpoints::RESULTS, 4],
            [Format::SOAP, ApiEndpoints::RESULTS, 5],
            // Для endpoint-а results с чеком
            [Format::CSV, ApiEndpoints::FISCAL_RESULTS, 1],
            [Format::WDDX, ApiEndpoints::FISCAL_RESULTS, 2],
            [Format::BRACKETS, ApiEndpoints::FISCAL_RESULTS, 3],
            [Format::XML, ApiEndpoints::FISCAL_RESULTS, 4],
            [Format::SOAP, ApiEndpoints::FISCAL_RESULTS, 5],
        ];
    }

    /**
     * Проверяет корректное преобразование формата в код Uniteller для endpoint-ов
     *
     * @dataProvider dataProviderResolve
     *
     * @throws \Tmconsulting\Uniteller\Exception\FormatNotSupportedException
     *
     * @covers \Tmconsulting\Uniteller\Request\Format::resolve
     */
    public function testResolveValidFormat(string $format, string $requestName, int $expected)
    {
        $result = Format::resolve($format, $requestName);
        $this->assertSame($expected, $result);
    }

    /**
     * Проверяет, что resolve() выбрасывает исключение для неподдерживаемого endpoint-а
     *
     * @covers \Tmconsulting\Uniteller\Request\Format::resolve
     */
    public function testResolveThrowsExceptionForUnsupportedEndpoint()
    {
        $this->expectException(EndpointNotSupportedException::class);

        Format::resolve(Format::CSV, 'invalid_endpoint');
    }

    /**
     * Проверяет, что resolve() выбрасывает исключение для неподдерживаемого формата у endpoint-а
     *
     * @covers \Tmconsulting\Uniteller\Request\Format::resolve
     */
    public function testResolveThrowsExceptionForUnsupportedFormatForEndpoint()
    {
        $this->expectException(FormatNotSupportedException::class);
        $this->expectExceptionMessage('Format [invalid_format] not supported for endpoint [' . ApiEndpoints::CARD . '].');

        Format::resolve('invalid_format', ApiEndpoints::CARD);
    }

    public static function dataProviderGetParserByFormat(): array
    {
        return [
            [Format::CSV, ParserCsv::class],
            [Format::XML, ParserXml::class],
            [Format::JSON, ParserJson::class],
        ];
    }

    /**
     * Проверяет, что getParserByFormat() возвращает соответствующий parser
     *
     * @dataProvider dataProviderGetParserByFormat
     *
     * @covers \Tmconsulting\Uniteller\Request\Format::getParserByFormat
     */
    public function testGetParserByFormat(string $format, $expected)
    {
        $parserClass = Format::getParserByFormat($format);
        $this->assertSame($expected, $parserClass);
    }

    /**
     * Проверяет, что getParserByFormat() выбрасывает исключение, если парсер для формата не реализован
     *
     * @covers \Tmconsulting\Uniteller\Request\Format::getParserByFormat
     */
    public function testGetParserByFormatThrowsExceptionIfParserIsNotImplemented()
    {
        $this->expectException(ParserNotImplementedException::class);
        $this->expectExceptionMessage('Parser for format [' . Format::SOAP . '] is not implemented.');

        Format::getParserByFormat(Format::SOAP);
    }

    /**
     * Проверяет, что getSupportedForEndpoint() выбрасывает исключение для неподдерживаемого endpoint-а
     *
     * @covers \Tmconsulting\Uniteller\Request\Format::getSupportedForEndpoint
     */
    public function testGetSupportedForEndpointThrowsExceptionForUnsupportedEndpoint()
    {
        $this->expectException(EndpointNotSupportedException::class);
        $this->expectExceptionMessage('Endpoint [invalid_endpoint] not supported here.');

        Format::getSupportedForEndpoint('invalid_endpoint');
    }

    /**
     * Возвращает корректные наборы поддерживаемых форматов для endpoint-ов
     */
    public static function dataProviderGetSupportedForEndpoint(): array
    {
        return [
            'results' => [
                ApiEndpoints::RESULTS,
                [
                    Format::CSV      => 1,
                    Format::WDDX     => 2,
                    Format::BRACKETS => 3,
                    Format::XML      => 4,
                    Format::SOAP     => 5,
                ],
            ],
            'cancel' => [
                ApiEndpoints::CANCEL,
                [
                    Format::CSV  => 1,
                    Format::WDDX => 2,
                    Format::XML  => 3,
                    Format::SOAP => 4,
                ],
            ],
            'card' => [
                ApiEndpoints::CARD,
                [
                    Format::CSV  => 1,
                    Format::WDDX => 2,
                    Format::XML  => 3,
                ],
            ],
            'confirm' => [
                ApiEndpoints::CONFIRM,
                [
                    Format::CSV  => 1,
                    Format::WDDX => 2,
                    Format::XML  => 3,
                ],
            ],
            'recurrent' => [
                ApiEndpoints::RECURRENT,
                [
                    Format::CSV => 1,
                ],
            ],
            'results_with_receipt' => [
                ApiEndpoints::FISCAL_RESULTS,
                [
                    Format::CSV      => 1,
                    Format::WDDX     => 2,
                    Format::BRACKETS => 3,
                    Format::XML      => 4,
                    Format::SOAP     => 5,
                ],
            ],
        ];
    }

    /**
     * Проверяет, что getSupportedForEndpoint() возвращает корректный набор форматов для endpoint-а
     *
     * @dataProvider dataProviderGetSupportedForEndpoint
     *
     * @covers \Tmconsulting\Uniteller\Request\Format::getSupportedForEndpoint
     */
    public function testGetSupportedForEndpointReturnsCorrectFormats(string $endpoint, array $expected)
    {
        $this->assertSame($expected, Format::getSupportedForEndpoint($endpoint));
    }
}
