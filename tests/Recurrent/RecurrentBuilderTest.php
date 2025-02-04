<?php
/**
 * Created by gitkv.
 * E-mail: gitkv@ya.ru
 * GitHub: gitkv
 */

namespace Tmconsulting\Uniteller\Tests\Recurrent;

use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Recurrent\RecurrentBuilder;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Recurrent\RecurrentBuilder
 */
class RecurrentBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $signatureCreator = $this->createMock(SignatureInterface::class);
        $signatureCreator->method('setFields')->willReturnSelf();
        $signatureCreator->method('createMd5')->willReturn('mocked_signature');
        $this->builder = new RecurrentBuilder($signatureCreator);
    }

    public static function dataProviderGetOrderIdp(): array
    {
        return [
            ['my101', 'my101'],
            [101, '101'],
            ['53c0714c-b036-408c-aeb6-58eb50f71098', '53c0714c-b036-408c-aeb6-58eb50f71098'],
        ];
    }

    /**
     * @dataProvider dataProviderGetOrderIdp
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetOrderIdp($set, string $get)
    {
        $this->builder->setOrderId($set);
        $this->assertEquals($get, $this->builder->getOrderId());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testSetOrderIdThrowsExceptionForTooLongOrderId()
    {
        $this->expectException(NotValidParameterException::class);
        $this->builder->setOrderId(str_repeat('a', 128));
    }

    public static function dataProviderGetSubtotalP(): array
    {
        return [
            [10000, 10000],
            ['10000.05', 10000.05],
            [10000.05, 10000.05],
        ];
    }

    /**
     * @dataProvider dataProviderGetSubtotalP
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetSubtotalP($set, float $get)
    {
        $this->builder->setSubtotalP($set);
        $this->assertIsFloat($this->builder->getSubtotalP());
        $this->assertEquals($get, $this->builder->getSubtotalP());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testSetSubtotalPThrowsExceptionForInvalidSubtotalP()
    {
        $this->expectException(RequiredParameterException::class);
        $this->builder->getSubtotalP();
    }

    public function testSetParentOrderIdp()
    {
        $parentOrderIdp = 'parent123';
        $this->builder->setParentOrderIdp($parentOrderIdp);
        $this->assertEquals($parentOrderIdp, $this->builder->getParentOrderIdp());
    }

    public function testSetParentShopIdp()
    {
        $parentShopIdp = 'shop123';
        $this->builder->setParentShopIdp($parentShopIdp);
        $this->assertEquals($parentShopIdp, $this->builder->getParentShopIdp());
    }

    public function testSetCustomerIdp()
    {
        $customerIdp = 'customer123';
        $this->builder->setCustomerIdp($customerIdp);
        $this->assertEquals($customerIdp, $this->builder->getCustomerIdp());
    }

    public function testSetCustomerIdpThrowsExceptionForTooLong()
    {
        $this->expectException(NotValidParameterException::class);
        $this->builder->setCustomerIdp(str_repeat('a', 65));
    }

    public function testSetCallbackFormat()
    {
        $callbackFormat = 'json';
        $this->builder->setCallbackFormat($callbackFormat);
        $this->assertEquals($callbackFormat, $this->builder->getCallbackFormat());
    }

    public function testGetSignature()
    {
        $this->builder->setShopId('shop')
            ->setPassword('secret')
            ->setOrderId('12345')
            ->setSubtotalP(100.50)
            ->setParentOrderIdp('parent123')
            ->setParentShopIdp('shop123')
            ->setCustomerIdp('customer123')
            ->setCallbackFormat('json');

        $signature = $this->builder->getSignature();

        $this->assertNotEmpty($signature);
        $this->assertEquals('mocked_signature', $signature);
    }

    public function testToArray()
    {
        $this->builder->setShopId('shop')
            ->setPassword('secret')
            ->setOrderId('12345')
            ->setSubtotalP(100.50)
            ->setParentOrderIdp('parent123')
            ->setParentShopIdp('shop123')
            ->setCustomerIdp('customer123')
            ->setCallbackFormat('json');

        $result = $this->builder->toArray();

        $this->assertArrayHasKey('Shop_IDP', $result);
        $this->assertArrayHasKey('Order_IDP', $result);
        $this->assertArrayHasKey('Subtotal_P', $result);
        $this->assertArrayHasKey('Parent_Order_IDP', $result);
        $this->assertArrayHasKey('Signature', $result);
    }

    public function testProcess()
    {
        $mockRequestManager = $this->createMock(RequestManager::class);
        $mockRequestManager->method('executeRequestAndParseResponseOrders')->willReturn('some_response');
        $mockRequestManager->method('setOptions')->willReturn($mockRequestManager);
        $container = $this->getMockBuilder(Container::class)
            ->onlyMethods(['get'])
            ->getMock();
        $container->method('get')->willReturn($mockRequestManager);

        $this->builder->setOrderId('12345');
        $this->builder->setSubtotalP(100.50);
        $this->builder->setParentOrderIdp('parent123');
        $this->builder->setParentShopIdp('shop123');

        $this->builder->setContainer($container);

        $result = $this->builder->process();

        $this->assertEquals('some_response', $result);
    }

    public function testGetRequestName()
    {
        $this->assertEquals(ApiEndpoints::RECURRENT, $this->builder->getEndpoint());
    }
}
