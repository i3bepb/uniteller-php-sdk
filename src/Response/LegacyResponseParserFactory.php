<?php

namespace Tmconsulting\Uniteller\Response;

use Tmconsulting\Uniteller\Exception\ParserNotImplementedException;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;

/**
 * Создаёт парсер результата для методов, возвращающих заказы в CSV или XML.
 */
class LegacyResponseParserFactory
{
    /** @var ParserReceiptFromBase64 Общий парсер фискальных чеков. */
    private $parserReceipt;

    /**
     * @param ParserReceiptFromBase64 $parserReceipt Парсер чеков, передаваемый парсеру результата.
     */
    public function __construct(ParserReceiptFromBase64 $parserReceipt)
    {
        $this->parserReceipt = $parserReceipt;
    }

    /**
     * @param string $format Имя формата: Format::CSV или Format::XML.
     *
     * @return LegacyResponseParserInterface Новый парсер заказов и ошибок выбранного формата.
     *
     * @throws ParserNotImplementedException Если для формата нет парсера результата.
     */
    public function create(string $format): LegacyResponseParserInterface
    {
        switch ($format) {
            case Format::CSV:
                return new LegacyCsvResponseParser($this->parserReceipt);
            case Format::XML:
                return new LegacyXmlResponseParser($this->parserReceipt);
        }

        throw new ParserNotImplementedException('Legacy response parser for format [' . $format . '] is not implemented.');
    }
}
