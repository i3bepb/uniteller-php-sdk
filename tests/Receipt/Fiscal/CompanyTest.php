<?php

namespace Tmconsulting\Uniteller\Tests\Receipt\Fiscal;

use Tmconsulting\Uniteller\Receipt\Fiscal\Company;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Fiscal\Company
 */
class CompanyTest extends TestCase
{
    public function testJsonSerialize()
    {
        $company = new Company('ООО Ромашка', 1234567890);

        $expected = [
            'name' => 'ООО Ромашка',
            'inn' => 1234567890,
        ];

        $this->assertSame($expected, $company->jsonSerialize());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($company));
    }
}
