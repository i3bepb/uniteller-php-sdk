<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use Tmconsulting\Uniteller\Receipt\Receipt;
use Tmconsulting\Uniteller\Request\ParserJson;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Tests\TestCase;

class ParserJsonTest extends TestCase
{
    public function testParse()
    {
        $parser = new ParserJson(new ParserReceiptFromBase64());
        $response = $this->getStubContents('responseCancelWithReceipt', 'json');
        $data = $parser->parse($response);
        $this->assertEquals('00', $data['Code']);
        $this->assertEquals('0.00', $data['Balance']);
        $this->assertEquals('canceled', $data['Status']);
        $this->assertInstanceOf(Receipt::class, $data['Receipt'][0]);
    }
}
