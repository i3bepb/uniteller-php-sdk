<?php

namespace Tmconsulting\Uniteller\Tests\Results;

use Tmconsulting\Uniteller\Common\EMoneyType;
use Tmconsulting\Uniteller\Common\MeanType;
use Tmconsulting\Uniteller\Common\NameFieldsUniteller;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Results\Fields;
use Tmconsulting\Uniteller\Results\Format;
use Tmconsulting\Uniteller\Results\PaymentsResults;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Results\Success;
use Tmconsulting\Uniteller\Results\ZipFlag;
use Tmconsulting\Uniteller\Tests\TestCase;

class ResultsBuilderTest extends TestCase
{
    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdp()
    {
        $builder = new ResultsBuilder();
        $builder->setShopId('0009999');
        $this->assertEquals('0009999', $builder->getShopId());
    }

    /**
     * Set empty string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdpEmptyString()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Shop_ID empty or not set');
        $builder = new ResultsBuilder();
        $builder->setShopId('');
        $builder->getShopId();
    }

    /**
     * Not set shop id
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdpNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Shop_ID empty or not set');
        $builder = new ResultsBuilder();
        $builder->getShopId();
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetLogin()
    {
        $builder = new ResultsBuilder();
        $builder->setLogin('login');
        $this->assertEquals('login', $builder->getLogin());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetLoginEmptyString()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Login empty or not set');
        $builder = new ResultsBuilder();
        $builder->setLogin('');
        $builder->getLogin();
    }

    /**
     * Not set shop id
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetLoginNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Login empty or not set');
        $builder = new ResultsBuilder();
        $builder->getLogin();
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetPassword()
    {
        $builder = new ResultsBuilder();
        $builder->setPassword('password');
        $this->assertEquals('password', $builder->getPassword());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetPasswordEmptyString()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Password empty or not set');
        $builder = new ResultsBuilder();
        $builder->setPassword('');
        $builder->getPassword();
    }

    /**
     * Not set shop id
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetPasswordNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Password empty or not set');
        $builder = new ResultsBuilder();
        $builder->getPassword();
    }

    public static function dataProviderGetFormat(): array
    {
        return [
            [9, null, true],
            [Format::CSV, Format::CSV, false],
            [Format::WDDX, Format::WDDX, false],
            [Format::IN_BRACKETS, Format::IN_BRACKETS, false],
            [Format::XML, Format::XML, false],
            [Format::SOAP, Format::SOAP, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetFormat
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetFormat(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter Format, must be one of the values: ' . implode(',', Format::toArray()));
        }
        $builder = new ResultsBuilder();
        $builder->setFormat($set);
        $this->assertEquals($get, $builder->getFormat());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetFormatNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Format empty or not set');
        $builder = new ResultsBuilder();
        $builder->getFormat();
    }

    public static function dataProviderGetOrderIdp(): array
    {
        return [
            ['my101', 'my101'],
            [101, '101'],
            ['53c0714c-b036-408c-aeb6-58eb50f71098', '53c0714c-b036-408c-aeb6-58eb50f71098'],
            [0, '0'],
        ];
    }

    /**
     * @dataProvider dataProviderGetOrderIdp
     */
    public function testGetOrderIdp($set, string $get)
    {
        $builder = new ResultsBuilder();
        $builder->setOrderIdp($set);
        $this->assertEquals($get, $builder->getOrderIdp());
    }

    public function testGetOrderIdpNotSet()
    {
        $builder = new ResultsBuilder();
        $this->assertEquals('', $builder->getOrderIdp());
    }

    public static function dataProviderGetSuccess(): array
    {
        return [
            [9, null, true],
            [Success::FAILED, Success::FAILED, false],
            [Success::SUCCESSFUL, Success::SUCCESSFUL, false],
            [Success::ALL, Success::ALL, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetSuccess
     */
    public function testGetSuccess(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter Success, must be one of the values: ' . implode(',', Success::toArray()));
        }
        $builder = new ResultsBuilder();
        $builder->setSuccess($set);
        $this->assertEquals($get, $builder->getSuccess());
    }

    public function testGetSuccessNotSet()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getSuccess());
    }

    public function testGetStartNotSet()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getStartDay());
        $this->assertNull($builder->getStartHour());
        $this->assertNull($builder->getStartMin());
        $this->assertNull($builder->getStartMonth());
        $this->assertNull($builder->getStartYear());
    }

    public function testGetEndNotSet()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getEndDay());
        $this->assertNull($builder->getEndHour());
        $this->assertNull($builder->getEndMin());
        $this->assertNull($builder->getEndMonth());
        $this->assertNull($builder->getEndYear());
    }

    public function testGetStartOfChangeNotSet()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getStartDayOfChange());
        $this->assertNull($builder->getStartHourOfChange());
        $this->assertNull($builder->getStartMinOfChange());
        $this->assertNull($builder->getStartMonthOfChange());
        $this->assertNull($builder->getStartYearOfChange());
    }

    public function testGetEndOfChangeNotSet()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getEndDayOfChange());
        $this->assertNull($builder->getEndHourOfChange());
        $this->assertNull($builder->getEndMinOfChange());
        $this->assertNull($builder->getEndMonthOfChange());
        $this->assertNull($builder->getEndYearOfChange());
    }

    public static function dataProviderDateTime(): array
    {
        return [
            [\DateTime::createFromFormat('Y-m-d', '2015-09-34'), false, 4, 10, 2015, null, null],
            [\DateTime::createFromFormat('Y-m-d H:i', '2025-02-10 01:05'), true, 10, 2, 2025, '01', '05'],
            [\DateTime::createFromFormat('Y-m-d H:i', '2024-03-15 00:00'), true, 15, 3, 2024, '00', '00'],
        ];
    }

    /**
     * @dataProvider dataProviderDateTime
     */
    public function testSetStart(\DateTime $set, bool $useHourAndMin, ?int $day, ?int $month, ?int $year, ?string $hour, ?string $min)
    {
        $builder = new ResultsBuilder();
        $builder->setStart($set, $useHourAndMin);
        $this->assertEquals($day, $builder->getStartDay());
        $this->assertEquals($month, $builder->getStartMonth());
        $this->assertEquals($year, $builder->getStartYear());
        $this->assertEquals($hour, $builder->getStartHour());
        $this->assertEquals($min, $builder->getStartMin());
    }

    /**
     * @dataProvider dataProviderDateTime
     */
    public function testSetStartOfChange(\DateTime $set, bool $useHourAndMin, ?int $day, ?int $month, ?int $year, ?string $hour, ?string $min)
    {
        $builder = new ResultsBuilder();
        $builder->setStartOfChange($set, $useHourAndMin);
        $this->assertEquals($day, $builder->getStartDayOfChange());
        $this->assertEquals($month, $builder->getStartMonthOfChange());
        $this->assertEquals($year, $builder->getStartYearOfChange());
        $this->assertEquals($hour, $builder->getStartHourOfChange());
        $this->assertEquals($min, $builder->getStartMinOfChange());
    }

    /**
     * @dataProvider dataProviderDateTime
     */
    public function testSetEnd(\DateTime $set, bool $useHourAndMin, ?int $day, ?int $month, ?int $year, ?string $hour, ?string $min)
    {
        $builder = new ResultsBuilder();
        $builder->setEnd($set, $useHourAndMin);
        $this->assertEquals($day, $builder->getEndDay());
        $this->assertEquals($month, $builder->getEndMonth());
        $this->assertEquals($year, $builder->getEndYear());
        $this->assertEquals($hour, $builder->getEndHour());
        $this->assertEquals($min, $builder->getEndMin());
    }

    /**
     * @dataProvider dataProviderDateTime
     */
    public function testSetEndOfChange(\DateTime $set, bool $useHourAndMin, ?int $day, ?int $month, ?int $year, ?string $hour, ?string $min)
    {
        $builder = new ResultsBuilder();
        $builder->setEndOfChange($set, $useHourAndMin);
        $this->assertEquals($day, $builder->getEndDayOfChange());
        $this->assertEquals($month, $builder->getEndMonthOfChange());
        $this->assertEquals($year, $builder->getEndYearOfChange());
        $this->assertEquals($hour, $builder->getEndHourOfChange());
        $this->assertEquals($min, $builder->getEndMinOfChange());
    }

    public function testGetMeanTypeNotSet()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getMeanType());
    }

    public static function dataProviderGetMeanType(): array
    {
        return [
            [100, null, true],
            [MeanType::ANY, MeanType::ANY, false],
            [MeanType::VISA, MeanType::VISA, false],
            [MeanType::MASTERCARD, MeanType::MASTERCARD, false],
            [MeanType::DINERS_CLUB, MeanType::DINERS_CLUB, false],
            [MeanType::JCB, MeanType::JCB, false],
            [MeanType::AMERICAN_EXPRESS, MeanType::AMERICAN_EXPRESS, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetMeanType
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetMeanType(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter MeanType, must be one of the values: ' . implode(',', MeanType::toArray()));
        }
        $builder = new ResultsBuilder();
        $builder->setMeanType($set);
        $this->assertEquals($get, $builder->getMeanType());
    }

    public function testGetEMoneyTypeNotSet()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getEMoneyType());
    }

    public static function dataProviderGetEMoneyType(): array
    {
        return [
            [100, null, true],
            [EMoneyType::ANY, EMoneyType::ANY, false],
            [EMoneyType::YANDEX_MONEY, EMoneyType::YANDEX_MONEY, false],
            [EMoneyType::CASH, EMoneyType::CASH, false],
            [EMoneyType::QIWI_REST, EMoneyType::QIWI_REST, false],
            [EMoneyType::MOBI, EMoneyType::MOBI, false],
            [EMoneyType::WEBMONEY_WMR, EMoneyType::WEBMONEY_WMR, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetEMoneyType
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetEMoneyType(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter EMoneyType, must be one of the values: ' . implode(',', EMoneyType::toArray()));
        }
        $builder = new ResultsBuilder();
        $builder->setEMoneyType($set);
        $this->assertEquals($get, $builder->getEMoneyType());
    }

    public static function dataProviderGetPaymentsResults(): array
    {
        return [
            [100, null, true],
            [PaymentsResults::LAST_PAYMENT, PaymentsResults::LAST_PAYMENT, false],
            [PaymentsResults::SUCCESSFUL_PAYMENTS, PaymentsResults::SUCCESSFUL_PAYMENTS, false],
            [PaymentsResults::ALL_PAYMENTS, PaymentsResults::ALL_PAYMENTS, false],
            [PaymentsResults::LAST_PAYMENT_AND_SUCCESSFUL_RETURNS, PaymentsResults::LAST_PAYMENT_AND_SUCCESSFUL_RETURNS, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetPaymentsResults
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetPaymentsResults(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter PaymentsResults, must be one of the values: ' . implode(',', PaymentsResults::toArray()));
        }
        $builder = new ResultsBuilder();
        $builder->setPaymentsResults($set);
        $this->assertEquals($get, $builder->getPaymentsResults());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetShowPartlyCanceled()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getShowPartlyCanceled());
        $builder->setShowPartlyCanceled(1);
        $this->assertEquals(1, $builder->getShowPartlyCanceled());
        $builder->setShowPartlyCanceled(0);
        $this->assertEquals(0, $builder->getShowPartlyCanceled());
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Not valid parameter ShowPartlyCanceled, must be one of the values: 1, 0');
        $builder->setShowPartlyCanceled(3);
    }

    public static function dataProviderGetZipFlag(): array
    {
        return [
            [100, null, true],
            [ZipFlag::DEFAULT, ZipFlag::DEFAULT, false],
            [ZipFlag::FILE, ZipFlag::FILE, false],
            [ZipFlag::ZIP, ZipFlag::ZIP, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetZipFlag
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetZipFlag(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter ZipFlag, must be one of the values: ' . implode(',', ZipFlag::toArray()));
        }
        $builder = new ResultsBuilder();
        $builder->setZipFlag($set);
        $this->assertEquals($get, $builder->getZipFlag());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetHeader()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getHeader());
        $builder->setHeader(1);
        $this->assertEquals(1, $builder->getHeader());
        $builder->setHeader(0);
        $this->assertEquals(0, $builder->getHeader());
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Not valid parameter Header, must be one of the values: 1, 0');
        $builder->setHeader(3);
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetHeaderOne()
    {
        $builder = new ResultsBuilder();
        $this->assertNull($builder->getHeader1());
        $builder->setHeader1(1);
        $this->assertEquals(1, $builder->getHeader1());
        $builder->setHeader1(0);
        $this->assertEquals(0, $builder->getHeader1());
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Not valid parameter Header1, must be one of the values: 1, 0');
        $builder->setHeader1(3);
    }

    /**
     * @covers \Tmconsulting\Uniteller\Results\ResultsBuilder::toArray
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testNotSetNotExistToArray()
    {
        $builder = (new ResultsBuilder())
            ->setShopId('009999')
            ->setLogin('Roquie')
            ->setPassword('password')
            ->setFormat(Format::XML);
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::SHOP_ID, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::LOGIN, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::PASSWORD, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::FORMAT, $arr);

        $this->assertArrayNotHasKey(NameFieldsUniteller::SUCCESS, $builder->toArray());
        $builder->setSuccess(Success::ALL);
        $this->assertArrayHasKey(NameFieldsUniteller::SUCCESS, $builder->toArray());

        $arr = $builder->toArray();
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_DAY, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_MONTH, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_YEAR, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_HOUR, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_MIN, $arr);
        $builder->setStart(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'));
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::START_DAY, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_MONTH, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_YEAR, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_HOUR, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_MIN, $arr);
        $builder->setStart(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'), true);
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::START_DAY, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_MONTH, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_YEAR, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_HOUR, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_MIN, $arr);

        $this->assertArrayNotHasKey(NameFieldsUniteller::END_DAY, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_MONTH, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_YEAR, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_HOUR, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_MIN, $arr);
        $builder->setEnd(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'));
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::END_DAY, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_MONTH, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_YEAR, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_HOUR, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_MIN, $arr);
        $builder->setEnd(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'), true);
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::END_DAY, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_MONTH, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_YEAR, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_HOUR, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_MIN, $arr);

        $this->assertArrayNotHasKey(NameFieldsUniteller::START_DAY_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_MONTH_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_YEAR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_HOUR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_MIN_OF_CHANGE, $arr);
        $builder->setStartOfChange(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'));
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::START_DAY_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_MONTH_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_YEAR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_HOUR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::START_MIN_OF_CHANGE, $arr);
        $builder->setStartOfChange(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'), true);
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::START_DAY_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_MONTH_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_YEAR_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_HOUR_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::START_MIN_OF_CHANGE, $arr);

        $this->assertArrayNotHasKey(NameFieldsUniteller::END_DAY_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_MONTH_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_YEAR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_HOUR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_MIN_OF_CHANGE, $arr);
        $builder->setEndOfChange(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'));
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::END_DAY_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_MONTH_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_YEAR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_HOUR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(NameFieldsUniteller::END_MIN_OF_CHANGE, $arr);
        $builder->setEndOfChange(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'), true);
        $arr = $builder->toArray();
        $this->assertArrayHasKey(NameFieldsUniteller::END_DAY_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_MONTH_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_YEAR_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_HOUR_OF_CHANGE, $arr);
        $this->assertArrayHasKey(NameFieldsUniteller::END_MIN_OF_CHANGE, $arr);

        $this->assertArrayNotHasKey(NameFieldsUniteller::MEAN_TYPE, $builder->toArray());
        $builder->setMeanType(MeanType::VISA);
        $this->assertArrayHasKey(NameFieldsUniteller::MEAN_TYPE, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::E_MONEY_TYPE, $builder->toArray());
        $builder->setEMoneyType(EMoneyType::ANY);
        $this->assertArrayHasKey(NameFieldsUniteller::E_MONEY_TYPE, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::PAYMENTS_RESULTS, $builder->toArray());
        $builder->setPaymentsResults(PaymentsResults::ALL_PAYMENTS);
        $this->assertArrayHasKey(NameFieldsUniteller::PAYMENTS_RESULTS, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::SHOW_PARTLY_CANCELED, $builder->toArray());
        $builder->setShowPartlyCanceled(1);
        $this->assertArrayHasKey(NameFieldsUniteller::SHOW_PARTLY_CANCELED, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::ZIP_FLAG, $builder->toArray());
        $builder->setZipFlag(ZipFlag::ZIP);
        $this->assertArrayHasKey(NameFieldsUniteller::ZIP_FLAG, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::HEADER, $builder->toArray());
        $builder->setHeader(1);
        $this->assertArrayHasKey(NameFieldsUniteller::HEADER, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::HEADER1, $builder->toArray());
        $builder->setHeader1(1);
        $this->assertArrayHasKey(NameFieldsUniteller::HEADER1, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::DELIMITER, $builder->toArray());
        $builder->setDelimiter(';');
        $this->assertArrayHasKey(NameFieldsUniteller::DELIMITER, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::OPEN_DELIMITER, $builder->toArray());
        $builder->setOpenDelimiter('(');
        $this->assertArrayHasKey(NameFieldsUniteller::OPEN_DELIMITER, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::CLOSE_DELIMITER, $builder->toArray());
        $builder->setCloseDelimiter(')');
        $this->assertArrayHasKey(NameFieldsUniteller::CLOSE_DELIMITER, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::ROW_DELIMITER, $builder->toArray());
        $builder->setRowDelimiter('13,10');
        $this->assertArrayHasKey(NameFieldsUniteller::ROW_DELIMITER, $builder->toArray());

        $this->assertArrayNotHasKey(NameFieldsUniteller::S_FIELDS, $builder->toArray());
        $builder->setFields([Fields::CARD_IDP, Fields::CURRENCY]);
        $this->assertArrayHasKey(NameFieldsUniteller::S_FIELDS, $builder->toArray());
    }
}
