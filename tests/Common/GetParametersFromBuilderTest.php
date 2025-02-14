<?php

namespace Tmconsulting\Uniteller\Tests\Common;

use I3bepb\ReflectionForTest\AccessToMethod;
use Tmconsulting\Uniteller\Common\GetParametersFromBuilder;
use Tmconsulting\Uniteller\Common\NameFieldsUniteller;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Tests\TestCase;

class GetParametersFromBuilderTest extends TestCase
{
    use AccessToMethod;

    /**
     * Check that the array remains unchanged
     *
     * @throws \ReflectionException
     */
    public function testGetParametersWithArray()
    {
        $objectWithTrait = $this->getObjectForTrait(GetParametersFromBuilder::class);
        $arr = [
            NameFieldsUniteller::SHOP_IDP => '00000576',
            NameFieldsUniteller::ORDER_IDP => 100,
            NameFieldsUniteller::SUBTOTAL_P => 5000,
            NameFieldsUniteller::URL_RETURN_OK => 'https://google.com/pay?q=banana',
            NameFieldsUniteller::URL_RETURN_NO => 'https://google.com/pay?q=banana',
        ];
        $result = $this->privateMethodWithParameters($objectWithTrait, 'getParameters', [$arr]);
        $this->assertCount(5, $result);
        foreach ($arr as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * @throws \ReflectionException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetParametersWithBuilder()
    {
        $objectWithTrait = $this->getObjectForTrait(GetParametersFromBuilder::class);
        $builder = new PaymentBuilder();
        $builder->setShopId('0009999')
            ->setOrderIdp(100)
            ->setSubtotalP(5000)
            ->setUrlReturnOk('https://google.com/pay?q=banana')
            ->setUrlReturnNo('https://google.com/pay?q=banana');
        $result = $this->privateMethodWithParameters($objectWithTrait, 'getParameters', [$builder]);
        $this->assertCount(5, $result);
        $this->assertEquals($builder->getShopId(), $result[NameFieldsUniteller::SHOP_IDP]);
        $this->assertEquals($builder->getOrderIdp(), $result[NameFieldsUniteller::ORDER_IDP]);
        $this->assertEquals($builder->getSubtotalP(), $result[NameFieldsUniteller::SUBTOTAL_P]);
        $this->assertEquals($builder->getUrlReturnOk(), $result[NameFieldsUniteller::URL_RETURN_OK]);
        $this->assertEquals($builder->getUrlReturnNo(), $result[NameFieldsUniteller::URL_RETURN_NO]);
    }
}
