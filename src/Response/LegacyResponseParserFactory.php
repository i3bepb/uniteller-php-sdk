<?php

namespace Tmconsulting\Uniteller\Response;

use Tmconsulting\Uniteller\Exception\ParserNotImplementedException;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;

class LegacyResponseParserFactory
{
    /** @var ParserReceiptFromBase64 */
    private $parserReceipt;

    public function __construct(ParserReceiptFromBase64 $parserReceipt)
    {
        $this->parserReceipt = $parserReceipt;
    }

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
