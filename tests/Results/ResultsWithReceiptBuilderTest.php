<?php

namespace Tmconsulting\Uniteller\Tests\Results;

use Tmconsulting\Uniteller\Builder\Enum\BaseUri;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Results\FiscalResultsBuilder;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Results\FiscalResultsBuilder
 */
class ResultsWithReceiptBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $signatureCreator = $this->createMock(SignatureInterface::class);
        $this->builder = new FiscalResultsBuilder($signatureCreator);
    }

    public function testBaseUri()
    {
        $this->assertEquals(BaseUri::FISCAL, $this->builder->getBaseUri());
    }

    public function testGetRequestName()
    {
        $this->assertEquals(ApiEndpoints::FISCAL_RESULTS, $this->builder->getEndpoint());
    }
}
