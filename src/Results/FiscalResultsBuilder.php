<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\Request\ApiEndpoints;

class FiscalResultsBuilder extends ResultsBuilder
{
    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return ApiEndpoints::FISCAL_RESULTS;
    }
}
