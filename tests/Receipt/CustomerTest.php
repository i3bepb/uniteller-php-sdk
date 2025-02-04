<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use I3bepb\ReflectionForTest\AccessToMethod;
use I3bepb\ReflectionForTest\AccessToProperty;
use Tmconsulting\Uniteller\Receipt\Customer;
use Tmconsulting\Uniteller\Receipt\Enum\DocCode;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Customer
 */
class CustomerTest extends TestCase
{
    use AccessToMethod;
    use AccessToProperty;

    public function testJsonSerializeWithAllData()
    {
        $customer = new Customer(
            '88005553535',
            'customer@example.com',
            'merchant_id_123',
            'Иван Иванов',
            1234567890,
            '01.01.1990',
            '643',
            DocCode::RUSSIAN_FEDERATION_PASSPORT,
            'passport_data',
            'Москва, ул. Пушкина, д. 1'
        );

        $expected = [
            'phone'       => '88005553535',
            'email'       => 'customer@example.com',
            'id'          => 'merchant_id_123',
            'name'        => 'Иван Иванов',
            'inn'         => 1234567890,
            'birthday'    => '01.01.1990',
            'citizenship' => '643',
            'doccode'     => DocCode::RUSSIAN_FEDERATION_PASSPORT,
            'docdata'     => 'passport_data',
            'address'     => 'Москва, ул. Пушкина, д. 1'
        ];

        $this->assertSame($expected, $customer->jsonSerialize());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($customer));
    }

    public static function dataProviderSetInn(): array
    {
        return [
            ['132808730606', 132808730606],
            [132808730606, 132808730606],
        ];
    }

    /**
     * @dataProvider dataProviderSetInn
     */
    public function testSetInn($inn, $expected)
    {
        $customer = new Customer();
        $this->privateMethodWithParameters($customer, 'setInn', [$inn]);
        $this->assertEquals($expected, $this->getProtectedOrPrivatePropertyValue($customer, 'inn'));
    }
}
