<?php

namespace Tmconsulting\Uniteller\Tests\Builder;

use Tmconsulting\Uniteller\Builder\BaseBuilder;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\EMoneyType;
use Tmconsulting\Uniteller\Parameter\Enum\MeanType;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Builder\BaseBuilder
 */
class AbstractBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $signature = $this->createMock(SignatureInterface::class);
        $this->builder = $this->getMockForAbstractClass(BaseBuilder::class, [$signature]);
    }

    public static function dataProviderGet(): array
    {
        return [
            ['simply string', 'simply string', false],
            ['', null, true],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $requiredParameterException Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGet
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::setBaseUri
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getBaseUri
     */
    public function testSetAndGetBaseUri($set, $get, bool $requiredParameterException)
    {
        if ($requiredParameterException) {
            $this->expectException(RequiredParameterException::class);
            $this->expectExceptionMessage('Parameter baseUri empty or not set');
        }
        if ($set !== null) {
            $this->builder->setBaseUri($set);
        }
        $this->assertEquals($get, $this->builder->getBaseUri());
    }

    /**
     * @param $set
     * @param $get
     * @param bool $requiredParameterException Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGet
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::setShopId
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getShopId
     */
    public function testSetAndGetShopId($set, $get, bool $requiredParameterException)
    {
        if ($requiredParameterException) {
            $this->expectException(RequiredParameterException::class);
            $this->expectExceptionMessage('Parameter Shop_ID empty or not set');
        }
        if ($set !== null) {
            $this->builder->setShopId($set);
        }
        $this->assertEquals($get, $this->builder->getShopId());
    }

    /**
     * @param $set
     * @param $get
     * @param bool $requiredParameterException Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGet
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::setLogin
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getLogin
     */
    public function testSetAndGetLogin($set, $get, bool $requiredParameterException)
    {
        if ($requiredParameterException) {
            $this->expectException(RequiredParameterException::class);
            $this->expectExceptionMessage('Parameter Login empty or not set');
        }
        if ($set !== null) {
            $this->builder->setLogin($set);
        }
        $this->assertEquals($get, $this->builder->getLogin());
    }

    /**
     * @param $set
     * @param $get
     * @param bool $requiredParameterException Должно ли сработать исключение.
     *
     * @dataProvider dataProviderGet
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::setPassword
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getPassword
     */
    public function testSetAndGetPassword($set, $get, bool $requiredParameterException)
    {
        if ($requiredParameterException) {
            $this->expectException(RequiredParameterException::class);
            $this->expectExceptionMessage('Parameter Password empty or not set');
        }
        if ($set !== null) {
            $this->builder->setPassword($set);
        }
        $this->assertEquals($get, $this->builder->getPassword());
    }

    /**
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getMeanType
     */
    public function testSetAndGetMeanTypeNotSet()
    {
        $this->assertNull($this->builder->getMeanType());
    }

    public static function dataProviderGetMeanType(): array
    {
        return [
            [100, null, true],
            [MeanType::ANY_CARD, MeanType::ANY_CARD, false],
            [MeanType::VISA, MeanType::VISA, false],
            [MeanType::MASTERCARD, MeanType::MASTERCARD, false],
            [MeanType::DINERS_CLUB, MeanType::DINERS_CLUB, false],
            [MeanType::JCB, MeanType::JCB, false],
            [MeanType::AMERICAN_EXPRESS, MeanType::AMERICAN_EXPRESS, false],
        ];
    }

    /**
     * @param int $set
     * @param int|null $get
     * @param bool $exception
     *
     * @dataProvider dataProviderGetMeanType
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::setMeanType
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getMeanType
     */
    public function testSetAndGetMeanType(int $set, ?int $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage(
                'Not valid parameter MeanType, must be one of the values: ' . implode(',', MeanType::toArray())
            );
        }
        $this->builder->setMeanType($set);
        $this->assertEquals($get, $this->builder->getMeanType());
    }

    /**
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getEMoneyType
     */
    public function testGetEMoneyTypeNotSet()
    {
        $this->assertNull($this->builder->getEMoneyType());
    }

    public static function dataProviderGetEMoneyType(): array
    {
        return [
            [100, null, true],
            [EMoneyType::ANY, EMoneyType::ANY, false],
            [EMoneyType::YANDEX_MONEY, EMoneyType::YANDEX_MONEY, false],
            [EMoneyType::CASH, EMoneyType::CASH, false],
            [EMoneyType::QIWI_REST, EMoneyType::QIWI_REST, false],
            [EMoneyType::MOBI_MONEY, EMoneyType::MOBI_MONEY, false],
            [EMoneyType::WEBMONEY_WMR, EMoneyType::WEBMONEY_WMR, false],
        ];
    }

    /**
     * @param int $set
     * @param $get
     * @param bool $exception
     *
     * @dataProvider dataProviderGetEMoneyType
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::setEMoneyType
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getEMoneyType
     */
    public function testSetAndGetEMoneyType(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage(
                'Not valid parameter EMoneyType, must be one of the values: ' . implode(',', EMoneyType::toArray())
            );
        }
        $this->builder->setEMoneyType($set);
        $this->assertEquals($get, $this->builder->getEMoneyType());
    }

    public static function dataProviderGetFormat(): array
    {
        return [
            [Format::CSV, Format::CSV, false, ApiEndpoints::CARD],
            [Format::WDDX, Format::WDDX, false, ApiEndpoints::CARD],
            [Format::XML, Format::XML, false, ApiEndpoints::CARD],
            [Format::JSON, null, true, ApiEndpoints::CARD],
            [Format::XML, Format::XML, true, ApiEndpoints::RECURRENT],
            [Format::CSV, Format::CSV, false, ApiEndpoints::RECURRENT],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception
     * @param string $requestName
     *
     * @dataProvider dataProviderGetFormat
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::setFormat
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getFormat
     */
    public function testSetAndGetFormat($set, $get, bool $exception, string $requestName)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
        }
        $this->builder->expects($this->exactly(1))
            ->method('getRequestName')
            ->willReturn($requestName);

        $this->builder->setFormat($set);
        $this->assertEquals($get, $this->builder->getFormat());
    }

    public static function dataProviderGetRequestName(): array
    {
        return [
            [ApiEndpoints::PAYMENT],
            [ApiEndpoints::CARD],
            [ApiEndpoints::CONFIRM],
            [ApiEndpoints::RECURRENT],
            [ApiEndpoints::CANCEL],
            [ApiEndpoints::RESULTS],
            [ApiEndpoints::FISCAL_PAYMENT],
            [ApiEndpoints::FISCAL_PREAUTH_PAYMENT_WITH_ADVANCE_RECEIPT],
            [ApiEndpoints::FISCAL_CONFIRM],
            [ApiEndpoints::FISCAL_CANCEL],
            [ApiEndpoints::FISCAL_RESULTS],
            ['any'],
        ];
    }

    /**
     * @param string $requestName
     *
     * @dataProvider dataProviderGetRequestName
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getRequestName
     */
    public function testGetRequestName(string $requestName)
    {
        $this->builder->expects($this->exactly(1))
            ->method('getRequestName')
            ->willReturn($requestName);

        $this->assertEquals($requestName, $this->builder->getEndpoint());
    }

    public static function dataProviderGetFormatForRequest(): array
    {
        return [
            [Format::CSV, 1, false, ApiEndpoints::CARD],
            [Format::WDDX, 2, false, ApiEndpoints::CARD],
            [Format::XML, 3, false, ApiEndpoints::CARD],
            [Format::JSON, null, true, ApiEndpoints::CARD],
            [Format::BRACKETS, null, true, ApiEndpoints::CARD],
            [Format::SOAP, null, true, ApiEndpoints::CARD],
            ['any', null, true, ApiEndpoints::CARD],
            [Format::CSV, 1, false, ApiEndpoints::CONFIRM],
            [Format::WDDX, 2, false, ApiEndpoints::CONFIRM],
            [Format::XML, 3, false, ApiEndpoints::CONFIRM],
            [Format::JSON, null, true, ApiEndpoints::CONFIRM],
            [Format::BRACKETS, null, true, ApiEndpoints::CONFIRM],
            [Format::SOAP, null, true, ApiEndpoints::CONFIRM],
            ['any', null, true, ApiEndpoints::CONFIRM],
            [Format::CSV, 1, false, ApiEndpoints::RECURRENT],
            [Format::WDDX, null, true, ApiEndpoints::RECURRENT],
            [Format::XML, null, true, ApiEndpoints::RECURRENT],
            [Format::JSON, null, true, ApiEndpoints::RECURRENT],
            [Format::BRACKETS, null, true, ApiEndpoints::RECURRENT],
            [Format::SOAP, null, true, ApiEndpoints::RECURRENT],
            ['any', null, true, ApiEndpoints::RECURRENT],
            [Format::CSV, 1, false, ApiEndpoints::CANCEL],
            [Format::WDDX, 2, false, ApiEndpoints::CANCEL],
            [Format::XML, 3, false, ApiEndpoints::CANCEL],
            [Format::JSON, null, true, ApiEndpoints::CANCEL],
            [Format::BRACKETS, null, true, ApiEndpoints::CANCEL],
            [Format::SOAP, 4, false, ApiEndpoints::CANCEL],
            ['any', null, true, ApiEndpoints::CANCEL],
            [Format::CSV, 1, false, ApiEndpoints::RESULTS],
            [Format::WDDX, 2, false, ApiEndpoints::RESULTS],
            [Format::BRACKETS, 3, false, ApiEndpoints::RESULTS],
            [Format::XML, 4, false, ApiEndpoints::RESULTS],
            [Format::SOAP, 5, false, ApiEndpoints::RESULTS],
            [Format::JSON, null, true, ApiEndpoints::RESULTS],
            ['any', null, true, ApiEndpoints::RESULTS],
            [Format::CSV, 1, false, ApiEndpoints::FISCAL_RESULTS],
            [Format::WDDX, 2, false, ApiEndpoints::FISCAL_RESULTS],
            [Format::BRACKETS, 3, false, ApiEndpoints::FISCAL_RESULTS],
            [Format::XML, 4, false, ApiEndpoints::FISCAL_RESULTS],
            [Format::SOAP, 5, false, ApiEndpoints::FISCAL_RESULTS],
            [Format::JSON, null, true, ApiEndpoints::FISCAL_RESULTS],
            ['any', null, true, ApiEndpoints::FISCAL_RESULTS],
        ];
    }

    /**
     * @param $set
     * @param $get
     * @param bool $exception
     * @param string $requestName
     *
     * @dataProvider dataProviderGetFormatForRequest
     *
     * @throws \Tmconsulting\Uniteller\Exception\FormatNotSupportedException
     *
     * @covers       \Tmconsulting\Uniteller\Builder\BaseBuilder::getFormatForRequest
     */
    public function testGetFormatForRequest($set, $get, bool $exception, string $requestName)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
        }
        $this->builder->expects($this->any())
            ->method('getRequestName')
            ->willReturn($requestName);

        $this->builder->setFormat($set);
        $this->assertEquals($get, $this->builder->getFormatCode());
    }
}
