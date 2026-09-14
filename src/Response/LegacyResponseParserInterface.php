<?php

namespace Tmconsulting\Uniteller\Response;

use Tmconsulting\Uniteller\Request\DecodedResponse;

interface LegacyResponseParserInterface
{
    /** @return \Tmconsulting\Uniteller\Order\Order[] */
    public function parse(DecodedResponse $response): array;
}
