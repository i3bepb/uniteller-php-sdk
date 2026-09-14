<?php

namespace Tmconsulting\Uniteller\Request;

use ParseCsv\Csv;

class ParserCsv implements ParserInterface
{
    public function parse(string $response): array
    {
        $csv = new Csv();
        $csv->auto($response);

        return $csv->data;
    }
}
