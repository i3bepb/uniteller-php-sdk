<?php

namespace Tmconsulting\Uniteller\Cancel;

use Tmconsulting\Uniteller\Exception\ExceptionFactory;
use Tmconsulting\Uniteller\Request\DecodedResponse;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;

class CancelResultParser
{
    /** @var ParserReceiptFromBase64 */
    private $parserReceipt;

    public function __construct(ParserReceiptFromBase64 $parserReceipt)
    {
        $this->parserReceipt = $parserReceipt;
    }

    public function parse(DecodedResponse $response): array
    {
        $data = $response->getData();
        if (!empty($data['Receipt'])) {
            $data['Receipt'] = $this->parserReceipt->parse($data['Receipt']);
        }
        if (!empty($data['Code']) && $data['Code'] !== '00') {
            throw ExceptionFactory::create(
                $data['Code'], $data['Note'], $response->getRequest(), $response->getResponse()
            );
        }

        return $data;
    }
}
