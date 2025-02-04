<?php

namespace Tmconsulting\Uniteller\Tests\Results;

use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\EMoneyType;
use Tmconsulting\Uniteller\Parameter\Enum\MeanType;
use Tmconsulting\Uniteller\Parameter\Enum\PaymentsResults;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Parameter\Enum\Success;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\ZipFlag;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Results\ResultsBuilder
 */
class ResultsBuilderTest extends TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $signatureCreator = $this->createMock(SignatureInterface::class);

        $this->builder = new ResultsBuilder($signatureCreator);
        $this->builder->setLogger($this->logger);

        // Настройка базовых параметров, необходимых для всех запросов
        $this->builder->setShopId('test_shop');
        $this->builder->setLogin('test_login');
        $this->builder->setPassword('test_password');
        $this->builder->setFormat(Format::XML);
        $this->builder->setBaseUri('https://api.uniteller.test');
    }

    public static function dataProviderSetGetOrderId(): array
    {
        return [
            [101, '101'],
            ['53c0714c-b036-408c-aeb6-58eb50f71098', '53c0714c-b036-408c-aeb6-58eb50f71098'],
        ];
    }

    /**
     * @param $set
     * @param string $get
     *
     * @dataProvider dataProviderSetGetOrderId
     */
    public function testSetGetOrderId($set, string $get)
    {
        $this->builder->setOrderId($set);
        $this->assertEquals($get, $this->builder->getOrderId());
    }

    public static function dataProviderSetGetSuccess(): array
    {
        return [
            [Success::SUCCESSFUL, 1],
            [Success::ALL, 2],
            [Success::FAILED, 0],
        ];
    }

    /**
     * @dataProvider dataProviderSetGetSuccess
     */
    public function testSetGetSuccess($set, $get)
    {
        $this->builder->setSuccess($set);
        $this->assertEquals($get, $this->builder->getSuccess());
    }

    public function testSetSuccessInvalid()
    {
        $this->expectException(NotValidParameterException::class);
        $this->builder->setSuccess(999);
    }

    public function testGetSuccessNotSet()
    {
        $this->assertNull($this->builder->getSuccess());
    }

    public function testDateSetters()
    {
        $date = new \DateTime('2023-01-15 14:30:00');

        // Тест для setStart
        $this->builder->setStart($date, true);
        $this->assertEquals(15, $this->builder->getStartDay());
        $this->assertEquals(1, $this->builder->getStartMonth());
        $this->assertEquals(2023, $this->builder->getStartYear());
        $this->assertEquals('14', $this->builder->getStartHour());
        $this->assertEquals('30', $this->builder->getStartMin());

        // Тест для setEnd
        $this->builder->setEnd($date, false);
        $this->assertEquals(15, $this->builder->getEndDay());
        $this->assertEquals(1, $this->builder->getEndMonth());
        $this->assertEquals(2023, $this->builder->getEndYear());
        $this->assertNull($this->builder->getEndHour());
        $this->assertNull($this->builder->getEndMin());
    }

    public static function dataProviderSetGetPaymentsResultsValid(): array
    {
        return [
            [PaymentsResults::ALL_PAYMENTS, 2],
            [PaymentsResults::LAST_PAYMENT, 0],
            [PaymentsResults::SUCCESSFUL_PAYMENTS, 1],
            [PaymentsResults::LAST_PAYMENT_AND_SUCCESSFUL_RETURNS, 3],
        ];
    }

    /**
     * @dataProvider dataProviderSetGetPaymentsResultsValid
     */
    public function testSetGetPaymentsResultsValid($set, $get)
    {
        $this->builder->setPaymentsResults($set);
        $this->assertEquals($get, $this->builder->getPaymentsResults());
    }

    public function testSetPaymentsResultsInvalid()
    {
        $this->expectException(NotValidParameterException::class);
        $this->builder->setPaymentsResults(999);
    }

    public static function dataProviderSetGetZipFlagValid(): array
    {
        return [
            [ZipFlag::ZIP, 2],
            [ZipFlag::FILE, 1],
            [ZipFlag::DEFAULT, 0],
        ];
    }

    /**
     * @dataProvider dataProviderSetGetZipFlagValid
     */
    public function testSetGetZipFlagValid($set, $get)
    {
        $this->builder->setZipFlag($set);
        $this->assertEquals($get, $this->builder->getZipFlag());
    }

    public function testSetZipFlagInvalid()
    {
        $this->expectException(NotValidParameterException::class);
        $this->builder->setZipFlag(999);
    }

    public static function dataProviderSetGetDelimiter(): array
    {
        return [
            [';', ';'],
            ['[', '['],
            [',', ','],
            [':', ':'],
            ['/', '/'],
        ];
    }

    /**
     * @dataProvider dataProviderSetGetDelimiter
     */
    public function testSetGetDelimiter($set, $get)
    {
        $this->builder->setDelimiter($set);
        $this->assertEquals($get, $this->builder->getDelimiter());
    }

    /**
     * @dataProvider dataProviderSetGetDelimiter
     */
    public function testSetGetOpenDelimiter($set, $get)
    {
        $this->builder->setOpenDelimiter($set);
        $this->assertEquals($get, $this->builder->getOpenDelimiter());
    }

    /**
     * @dataProvider dataProviderSetGetDelimiter
     */
    public function testSetGetCloseDelimiter($set, $get)
    {
        $this->builder->setCloseDelimiter($set);
        $this->assertEquals($get, $this->builder->getCloseDelimiter());
    }

    /**
     * @dataProvider dataProviderSetGetDelimiter
     */
    public function testSetGetRowDelimiter($set, $get)
    {
        $this->builder->setRowDelimiter($set);
        $this->assertEquals($get, $this->builder->getRowDelimiter());
    }

    public function testSetFields()
    {
        $fields = ['field1', 'field2', 'field3'];
        $this->builder->setFields($fields);
        $this->assertEquals('field1;field2;field3', $this->builder->getFields());
    }

    public function testGetRequestName()
    {
        $this->assertEquals(ApiEndpoints::RESULTS, $this->builder->getEndpoint());
    }

    public function testProcessSuccess()
    {
        $expectedResult = ['result' => 'success'];

        $requestManager = $this->createMock(RequestManager::class);
        $requestManager->method('executeRequestAndParseResponseOrders')->with($this->builder)->willReturn($expectedResult);
        $requestManager->method('setOptions')->willReturn($requestManager);

        $container = $this->createMock(Container::class);
        $container->method('get')->with(RequestManager::class)->willReturn($requestManager);
        $container->method('set')->willReturn(true);

        $this->builder->setContainer($container);

        // Включение debug режима
        $this->builder->setDebug(true);
        $this->logger->expects($this->once())
            ->method('debug');

        $result = $this->builder->process();

        $this->assertEquals($expectedResult, $result);
    }

    public function testProcessFailure()
    {
        $exception = new \Exception('Test exception');

        $requestManager = $this->createMock(RequestManager::class);
        $requestManager->method('executeRequestAndParseResponseOrders')->willThrowException($exception);
        $requestManager->method('setOptions')->willReturn($requestManager);

        $container = $this->createMock(Container::class);
        $container->method('get')->with(RequestManager::class)->willReturn($requestManager);
        $container->method('set')->willReturn(true);

        $this->builder->setContainer($container);

        $this->logger->expects($this->once())->method('error');

        $this->expectException(\Exception::class);
        $this->builder->process();
    }

    /**
     * Попытка отключить заголовки для CSV
     */
    public function testGetHeader1ForCsv()
    {
        $this->builder->setFormat(Format::CSV);
        $this->builder->setHeader1(0);

        // Для CSV заголовки всегда должны быть включены
        $this->assertEquals(1, $this->builder->getHeader1());
    }

    public function testGetStartNotSet()
    {
        $this->assertNull($this->builder->getStartDay());
        $this->assertNull($this->builder->getStartHour());
        $this->assertNull($this->builder->getStartMin());
        $this->assertNull($this->builder->getStartMonth());
        $this->assertNull($this->builder->getStartYear());
    }

    public function testGetEndNotSet()
    {
        $this->assertNull($this->builder->getEndDay());
        $this->assertNull($this->builder->getEndHour());
        $this->assertNull($this->builder->getEndMin());
        $this->assertNull($this->builder->getEndMonth());
        $this->assertNull($this->builder->getEndYear());
    }

    public function testGetStartOfChangeNotSet()
    {
        $this->assertNull($this->builder->getStartDayOfChange());
        $this->assertNull($this->builder->getStartHourOfChange());
        $this->assertNull($this->builder->getStartMinOfChange());
        $this->assertNull($this->builder->getStartMonthOfChange());
        $this->assertNull($this->builder->getStartYearOfChange());
    }

    public function testGetEndOfChangeNotSet()
    {
        $this->assertNull($this->builder->getEndDayOfChange());
        $this->assertNull($this->builder->getEndHourOfChange());
        $this->assertNull($this->builder->getEndMinOfChange());
        $this->assertNull($this->builder->getEndMonthOfChange());
        $this->assertNull($this->builder->getEndYearOfChange());
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
    public function testSetStart(
        \DateTime $set,
        bool $useHourAndMin,
        ?int $day,
        ?int $month,
        ?int $year,
        ?string $hour,
        ?string $min
    ) {
        $this->builder->setStart($set, $useHourAndMin);
        $this->assertEquals($day, $this->builder->getStartDay());
        $this->assertEquals($month, $this->builder->getStartMonth());
        $this->assertEquals($year, $this->builder->getStartYear());
        $this->assertEquals($hour, $this->builder->getStartHour());
        $this->assertEquals($min, $this->builder->getStartMin());
    }

    /**
     * @dataProvider dataProviderDateTime
     */
    public function testSetStartOfChange(
        \DateTime $set,
        bool $useHourAndMin,
        ?int $day,
        ?int $month,
        ?int $year,
        ?string $hour,
        ?string $min
    ) {
        $this->builder->setStartOfChange($set, $useHourAndMin);
        $this->assertEquals($day, $this->builder->getStartDayOfChange());
        $this->assertEquals($month, $this->builder->getStartMonthOfChange());
        $this->assertEquals($year, $this->builder->getStartYearOfChange());
        $this->assertEquals($hour, $this->builder->getStartHourOfChange());
        $this->assertEquals($min, $this->builder->getStartMinOfChange());
    }

    /**
     * @dataProvider dataProviderDateTime
     */
    public function testSetEnd(
        \DateTime $set,
        bool $useHourAndMin,
        ?int $day,
        ?int $month,
        ?int $year,
        ?string $hour,
        ?string $min
    ) {
        $this->builder->setEnd($set, $useHourAndMin);
        $this->assertEquals($day, $this->builder->getEndDay());
        $this->assertEquals($month, $this->builder->getEndMonth());
        $this->assertEquals($year, $this->builder->getEndYear());
        $this->assertEquals($hour, $this->builder->getEndHour());
        $this->assertEquals($min, $this->builder->getEndMin());
    }

    /**
     * @dataProvider dataProviderDateTime
     */
    public function testSetEndOfChange(
        \DateTime $set,
        bool $useHourAndMin,
        ?int $day,
        ?int $month,
        ?int $year,
        ?string $hour,
        ?string $min
    ) {
        $this->builder->setEndOfChange($set, $useHourAndMin);
        $this->assertEquals($day, $this->builder->getEndDayOfChange());
        $this->assertEquals($month, $this->builder->getEndMonthOfChange());
        $this->assertEquals($year, $this->builder->getEndYearOfChange());
        $this->assertEquals($hour, $this->builder->getEndHourOfChange());
        $this->assertEquals($min, $this->builder->getEndMinOfChange());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetShowPartlyCanceled()
    {
        $this->assertNull($this->builder->getShowPartlyCanceled());
        $this->builder->setShowPartlyCanceled(1);
        $this->assertEquals(1, $this->builder->getShowPartlyCanceled());
        $this->builder->setShowPartlyCanceled(0);
        $this->assertEquals(0, $this->builder->getShowPartlyCanceled());
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Not valid parameter ShowPartlyCanceled, must be one of the values: 1, 0');
        $this->builder->setShowPartlyCanceled(3);
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetHeader()
    {
        $this->assertNull($this->builder->getHeader());
        $this->builder->setHeader(1);
        $this->assertEquals(1, $this->builder->getHeader());
        $this->builder->setHeader(0);
        $this->assertEquals(0, $this->builder->getHeader());
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Not valid parameter Header, must be one of the values: 1, 0');
        $this->builder->setHeader(3);
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetHeaderOne()
    {
        $this->assertNull($this->builder->getHeader1());
        $this->builder->setHeader1(1);
        $this->assertEquals(1, $this->builder->getHeader1());
        $this->builder->setHeader1(0);
        $this->assertEquals(0, $this->builder->getHeader1());
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Not valid parameter Header1, must be one of the values: 1, 0');
        $this->builder->setHeader1(3);
    }

    /**
     * @covers \Tmconsulting\Uniteller\Results\ResultsBuilder::toArray
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testNotSetNotExistToArray()
    {
        $this->builder
            ->setShopId('009999')
            ->setLogin('Roquie')
            ->setPassword('password')
            ->setFormat(Format::XML);
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::SHOP_ID, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::LOGIN, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::PASSWORD, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::FORMAT, $arr);

        $this->assertArrayNotHasKey(UnitellerParameterName::SUCCESS, $this->builder->toArray());
        $this->builder->setSuccess(Success::ALL);
        $this->assertArrayHasKey(UnitellerParameterName::SUCCESS, $this->builder->toArray());

        $arr = $this->builder->toArray();
        $this->assertArrayNotHasKey(UnitellerParameterName::START_DAY, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_MONTH, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_YEAR, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_HOUR, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_MIN, $arr);
        $this->builder->setStart(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'));
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::START_DAY, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_MONTH, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_YEAR, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_HOUR, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_MIN, $arr);
        $this->builder->setStart(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'), true);
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::START_DAY, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_MONTH, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_YEAR, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_HOUR, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_MIN, $arr);

        $this->assertArrayNotHasKey(UnitellerParameterName::END_DAY, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_MONTH, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_YEAR, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_HOUR, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_MIN, $arr);
        $this->builder->setEnd(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'));
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::END_DAY, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_MONTH, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_YEAR, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_HOUR, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_MIN, $arr);
        $this->builder->setEnd(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'), true);
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::END_DAY, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_MONTH, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_YEAR, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_HOUR, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_MIN, $arr);

        $this->assertArrayNotHasKey(UnitellerParameterName::START_DAY_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_MONTH_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_YEAR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_HOUR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_MIN_OF_CHANGE, $arr);
        $this->builder->setStartOfChange(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'));
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::START_DAY_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_MONTH_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_YEAR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_HOUR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::START_MIN_OF_CHANGE, $arr);
        $this->builder->setStartOfChange(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'), true);
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::START_DAY_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_MONTH_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_YEAR_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_HOUR_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::START_MIN_OF_CHANGE, $arr);

        $this->assertArrayNotHasKey(UnitellerParameterName::END_DAY_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_MONTH_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_YEAR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_HOUR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_MIN_OF_CHANGE, $arr);
        $this->builder->setEndOfChange(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'));
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::END_DAY_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_MONTH_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_YEAR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_HOUR_OF_CHANGE, $arr);
        $this->assertArrayNotHasKey(UnitellerParameterName::END_MIN_OF_CHANGE, $arr);
        $this->builder->setEndOfChange(\DateTime::createFromFormat('Y-m-d H:i', '2025-01-24 00:00'), true);
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::END_DAY_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_MONTH_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_YEAR_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_HOUR_OF_CHANGE, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::END_MIN_OF_CHANGE, $arr);

        $this->assertArrayNotHasKey(UnitellerParameterName::MEAN_TYPE, $this->builder->toArray());
        $this->builder->setMeanType(MeanType::VISA);
        $this->assertArrayHasKey(UnitellerParameterName::MEAN_TYPE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::E_MONEY_TYPE, $this->builder->toArray());
        $this->builder->setEMoneyType(EMoneyType::ANY);
        $this->assertArrayHasKey(UnitellerParameterName::E_MONEY_TYPE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::PAYMENTS_RESULTS, $this->builder->toArray());
        $this->builder->setPaymentsResults(PaymentsResults::ALL_PAYMENTS);
        $this->assertArrayHasKey(UnitellerParameterName::PAYMENTS_RESULTS, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::SHOW_PARTLY_CANCELED, $this->builder->toArray());
        $this->builder->setShowPartlyCanceled(1);
        $this->assertArrayHasKey(UnitellerParameterName::SHOW_PARTLY_CANCELED, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::ZIP_FLAG, $this->builder->toArray());
        $this->builder->setZipFlag(ZipFlag::ZIP);
        $this->assertArrayHasKey(UnitellerParameterName::ZIP_FLAG, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::HEADER, $this->builder->toArray());
        $this->builder->setHeader(1);
        $this->assertArrayHasKey(UnitellerParameterName::HEADER, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::HEADER1, $this->builder->toArray());
        $this->builder->setHeader1(1);
        $this->assertArrayHasKey(UnitellerParameterName::HEADER1, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::DELIMITER, $this->builder->toArray());
        $this->builder->setDelimiter(';');
        $this->assertArrayHasKey(UnitellerParameterName::DELIMITER, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::OPEN_DELIMITER, $this->builder->toArray());
        $this->builder->setOpenDelimiter('(');
        $this->assertArrayHasKey(UnitellerParameterName::OPEN_DELIMITER, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::CLOSE_DELIMITER, $this->builder->toArray());
        $this->builder->setCloseDelimiter(')');
        $this->assertArrayHasKey(UnitellerParameterName::CLOSE_DELIMITER, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::ROW_DELIMITER, $this->builder->toArray());
        $this->builder->setRowDelimiter('13,10');
        $this->assertArrayHasKey(UnitellerParameterName::ROW_DELIMITER, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::S_FIELDS, $this->builder->toArray());
        $this->builder->setFields([SFields::CARD_IDP, SFields::CURRENCY]);
        $this->assertArrayHasKey(UnitellerParameterName::S_FIELDS, $this->builder->toArray());
    }
}
