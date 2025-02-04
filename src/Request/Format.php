<?php

namespace Tmconsulting\Uniteller\Request;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;
use Tmconsulting\Uniteller\Exception\EndpointNotSupportedException;
use Tmconsulting\Uniteller\Exception\FormatNotSupportedException;
use Tmconsulting\Uniteller\Exception\ParserNotImplementedException;

class Format
{
    use EnumToArrayTrait;

    /**
     * Поля разделены разделителем, указанным в поле Delimiter
     */
    const CSV = 'csv';
    /**
     * Web Distributed Data eXchange
     */
    const WDDX = 'wddx';
    /**
     * Каждое поле заключено в разделители, указанные в OpenDelimiter и CloseDelimiter
     */
    const BRACKETS = 'brackets';
    /**
     * XML
     */
    const XML = 'xml';
    /**
     * SOAP
     */
    const SOAP = 'soap';
    /**
     * Json
     */
    const JSON = 'json';

    /**
     * @var array
     */
    private static $variants = [
        ApiEndpoints::CARD => [
            self::CSV  => 1,
            self::WDDX => 2,
            self::XML  => 3,
        ],
        ApiEndpoints::CONFIRM => [
            self::CSV  => 1,
            self::WDDX => 2,
            self::XML  => 3,
        ],
        ApiEndpoints::RECURRENT => [
            self::CSV => 1,
        ],
        ApiEndpoints::CANCEL => [
            self::CSV  => 1,
            self::WDDX => 2,
            self::XML  => 3,
            self::SOAP => 4,
        ],
        ApiEndpoints::RESULTS => [
            self::CSV      => 1,
            self::WDDX     => 2,
            self::BRACKETS => 3,
            self::XML      => 4,
            self::SOAP     => 5,
        ],
        ApiEndpoints::FISCAL_RESULTS => [
            self::CSV      => 1,
            self::WDDX     => 2,
            self::BRACKETS => 3,
            self::XML      => 4,
            self::SOAP     => 5,
        ],
    ];

    /**
     * Преобразовывает выбранный формат в код Uniteller-а для определенного endpoint
     *
     * @param string $format Формат заданный строкой, например csv, xml, json и т.д.
     * @param string $endpoint
     *
     * @return int Code формата из API Uniteller
     *
     * @throws \Tmconsulting\Uniteller\Exception\FormatNotSupportedException
     * @throws \Tmconsulting\Uniteller\Exception\EndpointNotSupportedException
     */
    public static function resolve(string $format, string $endpoint): int
    {
        if (!array_key_exists($endpoint, self::$variants)) {
            throw new EndpointNotSupportedException('Endpoint [' . $endpoint . '] not supported here.');
        }
        if (!array_key_exists($format, self::$variants[$endpoint])) {
            throw new FormatNotSupportedException(
                'Format [' . $format . '] not supported for endpoint [' . $endpoint . '].'
            );
        }

        return self::$variants[$endpoint][$format];
    }

    /**
     * Возвращает класс парсера исходя из формата
     *
     * @param string $format
     *
     * @return class-string
     *
     * @throws \Tmconsulting\Uniteller\Exception\ParserNotImplementedException
     */
    public static function getParserByFormat(string $format): string
    {
        $arr = [
            self::CSV  => ParserCsv::class,
            self::XML  => ParserXml::class,
            self::JSON => ParserJson::class,
        ];
        if (!isset($arr[$format])) {
            throw new ParserNotImplementedException('Parser for format [' . $format . '] is not implemented.');
        }
        return $arr[$format];
    }

    /**
     * Возвращает поддерживаемые форматы ответа для данного endpoint-а
     *
     * @param string $endpoint
     *
     * @return array<string, int>
     *
     * @throws \Tmconsulting\Uniteller\Exception\EndpointNotSupportedException
     */
    public static function getSupportedForEndpoint(string $endpoint): array
    {
        if (!array_key_exists($endpoint, self::$variants)) {
            throw new EndpointNotSupportedException('Endpoint [' . $endpoint . '] not supported here.');
        }
        return self::$variants[$endpoint];
    }
}
