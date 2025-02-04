<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\Builder\BaseBuilder;
use Tmconsulting\Uniteller\Dependency\ContainerAwareInterface;
use Tmconsulting\Uniteller\Dependency\ContainerAwareTrait;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\EMoneyType;
use Tmconsulting\Uniteller\Parameter\Enum\MeanType;
use Tmconsulting\Uniteller\Parameter\Enum\PaymentsResults;
use Tmconsulting\Uniteller\Parameter\Enum\Success;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\ZipFlag;
use Tmconsulting\Uniteller\Parameter\EnumParameter;
use Tmconsulting\Uniteller\Parameter\FormatParameter;
use Tmconsulting\Uniteller\Parameter\IntParameter;
use Tmconsulting\Uniteller\Parameter\ScalarParameter;
use Tmconsulting\Uniteller\Parameter\SFieldsParameter;
use Tmconsulting\Uniteller\Parameter\StringParameter;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\RequestManager;

/**
 * Результаты оплат.
 *
 * @method $this setLogin(string|int $login) Логин. Доступен Merchant-у в Личном кабинете.
 * @method string|null getLogin()
 * @method bool hasLogin()
 *
 * @method $this setPassword(string|int $password) Пароль. Доступен Merchant-у в Личном кабинете.
 * @method string|null getPassword()
 * @method bool hasPassword()
 *
 * @method $this setFormat(string $format) Формат ответа.
 * @method string|null getFormat()
 * @method bool hasFormat()
 *
 * @method $this setOrderId(string|int $orderId) Идентификатор заказа в системе Merchant.
 * @method string|null getOrderId()
 * @method bool hasOrderId()
 *
 * @method $this setShopId(string|int $shopId) Идентификатор точки продажи в системе Uniteller.
 * @method string|null getShopId()
 * @method bool hasShopId()
 *
 * @method $this setSuccess(int $success) Какие операции включать в ответ.
 * @method string|null getSuccess()
 * @method bool hasSuccess()
 *
 * @method $this setStartDay(int $startDay)
 * @method string|null getStartDay()
 * @method bool hasStartDay()
 *
 * @method $this setStartMonth(int $startMonth)
 * @method string|null getStartMonth()
 * @method bool hasStartMonth()
 *
 * @method $this setStartYear(int $startYear)
 * @method string|null getStartYear()
 * @method bool hasStartYear()
 *
 * @method $this setStartHour(int $startHour)
 * @method string|null getStartHour()
 * @method bool hasStartHour()
 *
 * @method $this setStartMin(int $startMin)
 * @method string|null getStartMin()
 * @method bool hasStartMin()
 *
 * @method $this setEndDay(int $endDay)
 * @method string|null getEndDay()
 * @method bool hasEndDay()
 *
 * @method $this setEndMonth(int $endMonth)
 * @method string|null getEndMonth()
 * @method bool hasEndMonth()
 *
 * @method $this setEndYear(int $endYear)
 * @method string|null getEndYear()
 * @method bool hasEndYear()
 *
 * @method $this setEndHour(int $endHour)
 * @method string|null getEndHour()
 * @method bool hasEndHour()
 *
 * @method $this setEndMin(int $endMin)
 * @method string|null getEndMin()
 * @method bool hasEndMin()
 *
 * @method $this setStartDayOfChange(int $startDayOfChange)
 * @method string|null getStartDayOfChange()
 * @method bool hasStartDayOfChange()
 *
 * @method $this setStartMonthOfChange(int $startMonthOfChange)
 * @method string|null getStartMonthOfChange()
 * @method bool hasStartMonthOfChange()
 *
 * @method $this setStartYearOfChange(int $startYearOfChange)
 * @method string|null getStartYearOfChange()
 * @method bool hasStartYearOfChange()
 *
 * @method $this setStartHourOfChange(int $startHourOfChange)
 * @method string|null getStartHourOfChange()
 * @method bool hasStartHourOfChange()
 *
 * @method $this setStartMinOfChange(int $startMinOfChange)
 * @method string|null getStartMinOfChange()
 * @method bool hasStartMinOfChange()
 *
 * @method $this setEndDayOfChange(int $endDayOfChange)
 * @method string|null getEndDayOfChange()
 * @method bool hasEndDayOfChange()
 *
 * @method $this setEndMonthOfChange(int $endMonthOfChange)
 * @method string|null getEndMonthOfChange()
 * @method bool hasEndMonthOfChange()
 *
 * @method $this setEndYearOfChange(int $endYearOfChange)
 * @method string|null getEndYearOfChange()
 * @method bool hasEndYearOfChange()
 *
 * @method $this setEndHourOfChange(int $endHourOfChange)
 * @method string|null getEndHourOfChange()
 * @method bool hasEndHourOfChange()
 *
 * @method $this setEndMinOfChange(int $endMinOfChange)
 * @method string|null getEndMinOfChange()
 * @method bool hasEndMinOfChange()
 *
 * @method $this setMeanType(string $meanType) Тип платёжного средства.
 * @method string|null getMeanType()
 * @method bool hasMeanType()
 *
 * @method $this setEMoneyType(string $eMoneyType) Тип электронной валюты.
 * @method string|null getEMoneyType()
 * @method bool hasEMoneyType()
 *
 * @method $this setPaymentsResults(int $paymentsResults) Фильтр результатов оплат.
 * @method string|null getPaymentsResults()
 * @method bool hasPaymentsResults()
 *
 * @method $this setZipFlag(int $zipFlag) Режим выдачи результата.
 * @method string|null getZipFlag()
 * @method bool hasZipFlag()
 *
 * @method $this setHeader(int $header) Режим выдачи результата.
 * @method string|null getHeader()
 * @method bool hasHeader()
 *
 * @method $this setHeader1(int $header1) Режим выдачи результата.
 * @method string|null getHeader1()
 * @method bool hasHeader1()
 *
 * @method $this setDelimiter(int $delimiter) Разделитель полей в CVS-формате. Возможные варианты «;», «,», «:», «/».
 * @method string|null getDelimiter()
 * @method bool hasDelimiter()
 *
 * @method $this setOpenDelimiter(int $openDelimiter) Открывающий разделитель полей в формате «в скобках».
 * @method string|null getOpenDelimiter()
 * @method bool hasOpenDelimiter()
 *
 * @method $this setRowDelimiter(int $openDelimiter) Открывающий разделитель полей в формате «в скобках».
 * @method string|null getRowDelimiter()
 * @method bool hasRowDelimiter()
 *
 * @method $this setSFields(array $fields)
 * @method string|null getSFields()
 * @method bool hasSFields()
 */
class ResultsBuilder extends BaseBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return void
     */
    protected function registerParameters(): void
    {
        // Логин. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::LOGIN, UnitellerParameterName::LOGIN)
        );
        // Пароль. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::PASSWORD, UnitellerParameterName::PASSWORD)
        );
        // Идентификатор точки продажи в системе Uniteller.
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::SHOP_ID, UnitellerParameterName::SHOP_ID)
        );
        // Формат ответа.
        $this->parameters->add(
            new FormatParameter(
                CanonicalParameterName::FORMAT,
                UnitellerParameterName::FORMAT,
                Format::toArray(),
                $this->getEndpoint()
            )
        );
        // Идентификатор заказа в системе Merchant.
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::ORDER_ID, UnitellerParameterName::SHOP_ORDER_NUMBER)
        );
        /**
         * Какие операции включать в ответ.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\Success
         */
        $this->parameters->add(
            new EnumParameter(CanonicalParameterName::SUCCESS, UnitellerParameterName::SUCCESS, Success::toArray())
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::START_DAY, UnitellerParameterName::START_DAY, 1, 31)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::START_MONTH, UnitellerParameterName::START_MONTH, 1, 12)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::START_YEAR, UnitellerParameterName::START_YEAR, date('Y') - 2)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::START_HOUR, UnitellerParameterName::START_HOUR, 0, 23)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::START_MIN, UnitellerParameterName::START_MIN, 0, 59)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::END_DAY, UnitellerParameterName::END_DAY, 1, 31)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::END_MONTH, UnitellerParameterName::END_MONTH, 1, 12)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::END_YEAR, UnitellerParameterName::END_YEAR, date('Y') - 2)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::END_HOUR, UnitellerParameterName::END_HOUR, 0, 23)
        );
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::END_MIN, UnitellerParameterName::END_MIN, 0, 59)
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::START_DAY_OF_CHANGE,
                UnitellerParameterName::START_DAY_OF_CHANGE,
                1,
                31
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::START_MONTH_OF_CHANGE,
                UnitellerParameterName::START_MONTH_OF_CHANGE,
                1,
                12
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::START_YEAR_OF_CHANGE,
                UnitellerParameterName::START_YEAR_OF_CHANGE,
                date('Y') - 2
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::START_HOUR_OF_CHANGE,
                UnitellerParameterName::START_HOUR_OF_CHANGE,
                0,
                23
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::START_MIN_OF_CHANGE,
                UnitellerParameterName::START_MIN_OF_CHANGE,
                0,
                59
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::END_DAY_OF_CHANGE, UnitellerParameterName::END_DAY_OF_CHANGE, 1, 31
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::END_MONTH_OF_CHANGE,
                UnitellerParameterName::END_MONTH_OF_CHANGE,
                1,
                12
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::END_YEAR_OF_CHANGE,
                UnitellerParameterName::END_YEAR_OF_CHANGE,
                date('Y') - 2
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::END_HOUR_OF_CHANGE,
                UnitellerParameterName::END_HOUR_OF_CHANGE,
                0,
                23
            )
        );
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::END_MIN_OF_CHANGE, UnitellerParameterName::END_MIN_OF_CHANGE, 0, 59
            )
        );
        /**
         * Операции с каким типом платёжного средства нужно включать в отчёт.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\MeanType
         */
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::MEAN_TYPE,
                UnitellerParameterName::MEAN_TYPE,
                MeanType::toArray()
            )
        );
        /**
         * Операции с каким типом электронного платёжного средства нужно включать в отчёт.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\EMoneyType
         */
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::E_MONEY_TYPE,
                UnitellerParameterName::E_MONEY_TYPE,
                EMoneyType::toArray()
            )
        );
        /**
         * Фильтр результатов оплат (последняя, успешные, все, с возвратами).
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\PaymentsResults
         */
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::PAYMENTS_RESULTS,
                UnitellerParameterName::PAYMENTS_RESULTS,
                PaymentsResults::toArray()
            )
        );
        /**
         * Будет ли возвращаться в ответе параметр Partly canceled в случае частичной отмены/возврата платежа.
         * Возможные значения: 1 или 0 (или отсутствует)
         */
        $this->parameters->add(
            new IntParameter(
                CanonicalParameterName::SHOW_PARTLY_CANCELED, UnitellerParameterName::SHOW_PARTLY_CANCELED, 0, 1
            )
        );
        /**
         * Режим выдачи результата.
         * 0 — браузер, 1 — файл, 2 — архивированный файл. По умолчанию 0.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\ZipFlag
         */
        $this->parameters->add(
            new EnumParameter(CanonicalParameterName::ZIP_FLAG, UnitellerParameterName::ZIP_FLAG, ZipFlag::toArray())
        );
        /**
         * Будут ли возвращаться в ответе параметры запроса.
         * 0 — нет,
         * 1 — да
         * По умолчанию 0.
         */
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::HEADER, UnitellerParameterName::HEADER, 0, 1)
        );
        /**
         * Будут ли возвращаться в ответе заголовки полей.
         * 0 — нет,
         * 1 — да
         * По умолчанию 0.
         */
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::HEADER1, UnitellerParameterName::HEADER1, 0, 1)
        );
        // Разделитель полей в CVS-формате. Возможные варианты «;», «,», «:», «/».
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::DELIMITER,
                UnitellerParameterName::DELIMITER,
                [';', ',', ':', '/']
            )
        );
        // Открывающий разделитель полей в формате «в скобках». Возможные варианты «[», «{», «(».
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::OPEN_DELIMITER,
                UnitellerParameterName::OPEN_DELIMITER,
                ['[', '{', '(']
            )
        );
        // Закрывающий разделитель полей в формате «в скобках». Возможные варианты «]», «}», «)».
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::CLOSE_DELIMITER,
                UnitellerParameterName::CLOSE_DELIMITER,
                [']', '}', ')']
            )
        );
        // Разделитель строк. Возможные варианты «13», «10», «13,10», «10,13». По умолчанию «13,10».
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::ROW_DELIMITER,
                UnitellerParameterName::ROW_DELIMITER,
                ['13', '10', '13,10', '10,13']
            )
        );
        /**
         * Набор информационных полей, возвращаемых в ответе на запрос.
         * Если параметр не передаётся или передаётся пустое значение, то будет возвращён полный список полей.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\SFields
         */
        $this->parameters->add(
            new SFieldsParameter(CanonicalParameterName::S_FIELDS, UnitellerParameterName::S_FIELDS)
        );
    }

    /**
     * @param \DateTime $dateTime Начало периода создания заказа. Дата и время.
     * @param bool $useHourAndMin Использовать час и минуту.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setStart(\DateTime $dateTime, bool $useHourAndMin = false): ResultsBuilder
    {
        $this->setStartDay((int)$dateTime->format('j'));
        $this->setStartMonth((int)$dateTime->format('n'));
        $this->setStartYear((int)$dateTime->format('Y'));
        if ($useHourAndMin) {
            $this->setStartHour((int)$dateTime->format('H'));
            $this->setStartMin((int)$dateTime->format('i'));
        }

        return $this;
    }

    /**
     * @param \DateTime $dateTime Конец периода создания заказа. Дата и время.
     * @param bool $useHourAndMin Использовать час и минуту.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setEnd(\DateTime $dateTime, bool $useHourAndMin = false): ResultsBuilder
    {
        $this->setEndDay((int)$dateTime->format('j'));
        $this->setEndMonth((int)$dateTime->format('n'));
        $this->setEndYear((int)$dateTime->format('Y'));
        if ($useHourAndMin) {
            $this->setEndHour((int)$dateTime->format('H'));
            $this->setEndMin((int)$dateTime->format('i'));
        }

        return $this;
    }

    /**
     * @param \DateTime $dateTime Начало периода изменения заказа. Дата и время.
     * @param bool $useHourAndMin Использовать час и минуту.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setStartOfChange(\DateTime $dateTime, bool $useHourAndMin = false): ResultsBuilder
    {
        $this->setStartDayOfChange((int)$dateTime->format('j'));
        $this->setStartMonthOfChange((int)$dateTime->format('n'));
        $this->setStartYearOfChange((int)$dateTime->format('Y'));
        if ($useHourAndMin) {
            $this->setStartHourOfChange((int)$dateTime->format('H'));
            $this->setStartMinOfChange((int)$dateTime->format('i'));
        }

        return $this;
    }

    /**
     * @param \DateTime $dateTime Конец периода изменения заказа. Дата и время.
     * @param bool $useHourAndMin Использовать час и минуту.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setEndOfChange(\DateTime $dateTime, bool $useHourAndMin = false): ResultsBuilder
    {
        $this->setEndDayOfChange((int)$dateTime->format('j'));
        $this->setEndMonthOfChange((int)$dateTime->format('n'));
        $this->setEndYearOfChange((int)$dateTime->format('Y'));
        if ($useHourAndMin) {
            $this->setEndHourOfChange((int)$dateTime->format('H'));
            $this->setEndMinOfChange((int)$dateTime->format('i'));
        }

        return $this;
    }

    /**
     * Проверка присутствия обязательных полей.
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    protected function validateRequired(): void
    {
        if (!$this->hasLogin()) {
            throw new RequiredParameterException(UnitellerParameterName::LOGIN);
        }
        if (!$this->hasPassword()) {
            throw new RequiredParameterException(UnitellerParameterName::PASSWORD);
        }
        if (!$this->hasFormat()) {
            throw new RequiredParameterException(UnitellerParameterName::FORMAT);
        }
        if (!$this->hasShopId()) {
            throw new RequiredParameterException(UnitellerParameterName::SHOP_ID);
        }
    }

    /**
     * Return request name.
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return ApiEndpoints::RESULTS;
    }

    /**
     * @return string|null Формат ответа.
     */
    public function getResponseFormat(): ?string
    {
        if ($this->hasFormat()) {
            $arr = array_flip(Format::getSupportedForEndpoint($this->getEndpoint()));
            return $arr[$this->getFormat()];
        }
        return Format::CSV;
    }

    /**
     * @return \Tmconsulting\Uniteller\Order\Order[]
     *
     * @throws \Throwable
     * @throws \Tmconsulting\Uniteller\Exception\FormatNotSupportedException
     * @throws \Tmconsulting\Uniteller\Exception\EndpointNotSupportedException
     */
    public function process()
    {
        $this->container->set(ParserInterface::class, Format::getParserByFormat($this->getResponseFormat()));
        $request = $this->container->get(RequestManager::class);

        if ($this->debug) {
            $this->logger->debug('Parameters in request: ' . PHP_EOL . print_r($this->toArray(), true));
        }

        try {
            $result = $request->executeRequestAndParseResponseOrders($this);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            throw $e;
        }

        return $result;
    }
}
