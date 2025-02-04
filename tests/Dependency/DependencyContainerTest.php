<?php

namespace Tmconsulting\Uniteller\Tests\Dependency;

use I3bepb\ReflectionForTest\AccessToProperty;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tmconsulting\Uniteller\Callback\Callback;
use Tmconsulting\Uniteller\Cancel\CancelBuilder;
use Tmconsulting\Uniteller\Cancel\CancelWithReceiptBuilder;
use Tmconsulting\Uniteller\Confirm\ConfirmBuilder;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Dependency\ServiceNotFoundException;
use Tmconsulting\Uniteller\Payment\FastPaymentBuilder;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Payment\PaymentWithReceiptBuilder;
use Tmconsulting\Uniteller\Payment\PreAuthPaymentBuilder;
use Tmconsulting\Uniteller\Payment\PreAuthPaymentWithReceiptBuilder;
use Tmconsulting\Uniteller\Payment\RecurrentStartPaymentBuilder;
use Tmconsulting\Uniteller\Recurrent\RecurrentBuilder;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Request\ParserXml;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Results\FiscalResultsBuilder;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Dependency\Container
 */
class DependencyContainerTest extends TestCase
{
    use AccessToProperty;

    /**
     * @throws \ReflectionException
     *
     * @covers \Tmconsulting\Uniteller\Dependency\Container::__construct
     */
    public function testConstructor()
    {
        $container = new Container([LoggerInterface::class => new NullLogger()], true);

        // Проверяем, что resolved и debug были установлены корректно
        $this->assertTrue($container->has(LoggerInterface::class));
        $this->assertTrue($container->get(LoggerInterface::class) instanceof NullLogger);
        $this->assertTrue($this->getProtectedOrPrivatePropertyValue($container, 'debug'));
    }

    public static function dataProviderHas(): array
    {
        return [
            [PaymentBuilder::class, true],
            ['NotExistClass', false],
            [Callback::class, true],
            [CancelBuilder::class, true],
            [CancelWithReceiptBuilder::class, true],
            [ConfirmBuilder::class, true],
            [FastPaymentBuilder::class, true],
            [LoggerInterface::class, true],
            [ParserInterface::class, true],
            [ParserReceiptFromBase64::class, true],
            [PaymentBuilder::class, true],
            [PaymentWithReceiptBuilder::class, true],
            [PreAuthPaymentBuilder::class, true],
            [PreAuthPaymentWithReceiptBuilder::class, true],
            [FiscalConfirmBuilder::class, true],
            [RecurrentBuilder::class, true],
            [RecurrentStartPaymentBuilder::class, true],
            [RequestManager::class, true],
            [ResultsBuilder::class, true],
            [FiscalResultsBuilder::class, true],
            [SignatureInterface::class, true],
        ];
    }

    /**
     * @param string $class
     * @param bool $exist
     *
     * @dataProvider dataProviderHas
     *
     * @covers       \Tmconsulting\Uniteller\Dependency\Container::has
     */
    public function testHas(string $class, bool $exist)
    {
        $container = new Container();
        $this->assertEquals($exist, $container->has($class));
    }

    /**
     * @covers \Tmconsulting\Uniteller\Dependency\Container::get
     */
    public function testGet()
    {
        $container = new Container();
        $cancelBuilder = $container->get(CancelBuilder::class);
        $this->assertInstanceOf(CancelBuilder::class, $cancelBuilder);

        $this->expectException(ServiceNotFoundException::class);
        $container->get('NotExistClass');
    }

    /**
     * @covers \Tmconsulting\Uniteller\Dependency\Container::set
     */
    public function testSetResolve()
    {
        $container = new Container();
        $container->set(ParserInterface::class, new ParserXml(new ParserReceiptFromBase64()));

        $this->assertTrue($container->has(ParserInterface::class));
        $this->assertInstanceOf(ParserXml::class, $container->get(ParserInterface::class));

        $anyObject = (object)[
            'name'     => 'any',
            'property' => 'example',
        ];
        $container->set('abc', $anyObject);
        $obj = $container->get('abc');
        $ref = new \ReflectionObject($obj);
        $this->assertTrue($ref->hasProperty('name'));
        $this->assertTrue($ref->hasProperty('property'));
        $this->assertFalse($ref->hasProperty('foo'));
        $name = $ref->getProperty('name');
        $this->assertEquals('any', $name->getValue($obj));
        $property = $ref->getProperty('property');
        $this->assertEquals('example', $property->getValue($obj));
    }

    /**
     * Проверяем, что метод get корректно создаёт объекты с зависимостями
     *
     * @covers \Tmconsulting\Uniteller\Dependency\Container::get
     */
    public function testGetWithDependencies()
    {
        $container = new Container([
            \Psr\Http\Client\ClientInterface::class          => \GuzzleHttp\Client::class,
            \Psr\Http\Message\RequestFactoryInterface::class => \GuzzleHttp\Psr7\HttpFactory::class,
            \Psr\Http\Message\StreamFactoryInterface::class  => \GuzzleHttp\Psr7\HttpFactory::class,
        ]);
        $requestManager = $container->get(RequestManager::class);
        $this->assertInstanceOf(RequestManager::class, $requestManager);
        // Проверяем, что зависимости были установлены
        $this->assertInstanceOf(
            ParserInterface::class,
            $this->getProtectedOrPrivatePropertyValue($requestManager, 'parserOrders')
        );
        // Проверяем, что метод get устанавливает контейнер для объектов, реализующих ContainerAwareInterface
        $confirmBuilder = $container->get(ConfirmBuilder::class);
        $this->assertInstanceOf(
            ContainerInterface::class,
            $this->getProtectedOrPrivatePropertyValue($confirmBuilder, 'container')
        );
    }
}
