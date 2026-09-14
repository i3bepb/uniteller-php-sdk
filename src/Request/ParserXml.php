<?php

namespace Tmconsulting\Uniteller\Request;

use Mtownsend\XmlToArray\XmlToArray;
use Tmconsulting\Uniteller\Exception\InvalidResponseException;

class ParserXml implements ParserInterface
{
    public function parse(string $response): array
    {
        if (trim($response) === '') {
            throw new InvalidResponseException('Empty XML response');
        }

        $previous = libxml_use_internal_errors(true);
        try {
            // The converter does not check loadXML() failures itself.
            $document = new \DOMDocument();
            if (!$document->loadXML($response, LIBXML_NONET)) {
                throw new InvalidResponseException('Invalid XML response');
            }

            return XmlToArray::convert($response);
        } catch (\Throwable $e) {
            throw new InvalidResponseException('Cannot decode XML response', 0, $e);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
