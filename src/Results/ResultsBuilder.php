<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\ArraybleInterface;
use Tmconsulting\Uniteller\Builder\BaseBuilder;
use Tmconsulting\Uniteller\Builder\BuilderInterface;
use Tmconsulting\Uniteller\Common\NameFieldsUniteller;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;

class ResultsBuilder extends BaseBuilder implements ArraybleInterface, BuilderInterface
{
    /**
     * Формат выдачи результата.
     *
     * @see \Tmconsulting\Uniteller\Results\Format
     *
     * @var int
     */
    private $format;

    /**
     * Номер заказа в системе расчётов интернет-магазина.
     * Если этот параметр указывается, то ответ содержит результат авторизации по указанному заказу.
     * Если при запросе результатов авторизации по номеру заказа не указывается период операций
     * (параметры Start… и End…, см. ниже), то возвращаются результаты за всю историю операций с этим номером заказа.
     *
     * @var string
     */
    private $shopOrderNumber;

    /**
     * Какие операции включать в ответ. Например, неуспешные со статусами Waiting, Not Authorized.
     * Если не передавать параметр, то все.
     *
     * @see \Tmconsulting\Uniteller\Results\Success
     *
     * @var int
     */
    private $success;

    /**
     * Параметры периода создания заказа.
     *
     * При запросе результатов авторизации с указанием периода, операции за который будут включены в отчёт
     * (параметры Start… и End…), длительность указанного периода не может превышать 7 суток, в противном случае
     * запрос не будет выполнен с сообщением об ошибке.
     *
     * Если параметры Start…, End… не задаются, то по умолчанию запрос выполняется за последние 24 часа до момента запроса.
     *
     * @var int
     */
    private $startDay;
    /**
     * @var int
     */
    private $startMonth;
    /**
     * @var int
     */
    private $startYear;
    /**
     * @var string
     */
    private $startHour;
    /**
     * @var string
     */
    private $startMin;
    /**
     * @var int
     */
    private $endDay;
    /**
     * @var int
     */
    private $endMonth;
    /**
     * @var int
     */
    private $endYear;
    /**
     * @var string
     */
    private $endHour;
    /**
     * @var string
     */
    private $endMin;
    /**
     * @var int
     */
    private $startDayOfChange;
    /**
     * @var int
     */
    private $startMonthOfChange;
    /**
     * @var int
     */
    private $startYearOfChange;
    /**
     * @var string
     */
    private $startHourOfChange;
    /**
     * @var string
     */
    private $startMinOfChange;
    /**
     * @var int
     */
    private $endDayOfChange;
    /**
     * @var int
     */
    private $endMonthOfChange;
    /**
     * @var int
     */
    private $endYearOfChange;
    /**
     * @var string
     */
    private $endHourOfChange;
    /**
     * @var string
     */
    private $endMinOfChange;

    /**
     * Какую информацию по оплатам вернуть.
     *
     * @see \Tmconsulting\Uniteller\Results\PaymentsResults
     *
     * @var int
     */
    private $paymentsResults;

    /**
     * Будет ли возвращаться в ответе параметр Partly canceled в случае частичной отмены/возврата платежа.
     * Возможные значения: 1 или 0 (или отсутствует)
     *
     * @var int
     */
    private $showPartlyCanceled;

    /**
     * Режим выдачи результата.
     * По умолчанию 0.
     *
     * @see \Tmconsulting\Uniteller\Results\ZipFlag
     *
     * @var int
     */
    private $zipFlag;

    /**
     * Будут ли возвращаться в ответе параметры запроса.
     * 0 — нет,
     * 1 — да
     * По умолчанию 0.
     *
     * @var int
     */
    private $header;

    /**
     * Будут ли возвращаться в ответе заголовки полей.
     * 0 — нет,
     * 1 — да
     * По умолчанию 0.
     *
     * @var int
     */
    private $header1;

    /**
     * Разделитель полей в CVS-формате. Возможные варианты «;», «,», «:», «/».
     * Если указан другой символ, будет использован символ по умолчанию — «;»
     *
     * @var string
     */
    private $delimiter;

    /**
     * Открывающий разделитель полей в формате «в скобках». Возможные варианты «[», «{», «(».
     * Если указан другой символ, будет использован символ по умолчанию — «[».
     *
     * @var string
     */
    private $openDelimiter;

    /**
     * Закрывающий разделитель полей в формате «в скобках». Возможные варианты «]», «}», «)».
     * Если указан другой символ, будет использоваться символ по умолчанию — «]».
     *
     * @var string
     */
    private $closeDelimiter;

    /**
     * Разделитель строк. Возможные варианты «13», «10», «13,10», «10,13».
     * По умолчанию «13,10».
     *
     * @var string
     */
    private $rowDelimiter;

    /**
     * Набор информационных полей, возвращаемых в ответе на запрос.
     * Если параметр не передаётся или передаётся пустое значение, то будет возвращён полный список полей.
     *
     * @see \Tmconsulting\Uniteller\Results\Fields
     *
     * @var string
     */
    private $fields;

    /**
     * @param int $format Формат выдачи результата.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setFormat(int $format): ResultsBuilder
    {
        $variants = Format::toArray();
        if (!in_array($format, $variants, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::FORMAT . ', must be one of the values: ' . implode(',', $variants)
            );
        }
        $this->format = $format;

        return $this;
    }

    /**
     * @param string|int $orderIdp Номер заказа в системе интернет-магазина
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setOrderIdp($orderIdp): ResultsBuilder
    {
        $this->shopOrderNumber = (string)$orderIdp;

        return $this;
    }

    /**
     * @param int $success Какие операции включать в ответ. Константа
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setSuccess(int $success): ResultsBuilder
    {
        $variants = Success::toArray();
        if (!in_array($success, $variants, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::SUCCESS . ', must be one of the values: ' . implode(',', $variants)
            );
        }
        $this->success = $success;

        return $this;
    }

    /**
     * @param \DateTime $dateTime Начало периода создания заказа. Дата и время.
     * @param bool $useHourAndMin Использовать час и минуту.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setStart(\DateTime $dateTime, bool $useHourAndMin = false): ResultsBuilder
    {
        $this->startDay = (int)$dateTime->format('j');
        $this->startMonth = (int)$dateTime->format('n');
        $this->startYear = (int)$dateTime->format('Y');
        if ($useHourAndMin) {
            $this->startHour = $dateTime->format('H');
            $this->startMin = $dateTime->format('i');
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
        $this->endDay = (int)$dateTime->format('j');
        $this->endMonth = (int)$dateTime->format('n');
        $this->endYear = (int)$dateTime->format('Y');
        if ($useHourAndMin) {
            $this->endHour = $dateTime->format('H');
            $this->endMin = $dateTime->format('i');
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
        $this->startDayOfChange = (int)$dateTime->format('j');
        $this->startMonthOfChange = (int)$dateTime->format('n');
        $this->startYearOfChange = (int)$dateTime->format('Y');
        if ($useHourAndMin) {
            $this->startHourOfChange = $dateTime->format('H');
            $this->startMinOfChange = $dateTime->format('i');
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
        $this->endDayOfChange = (int)$dateTime->format('j');
        $this->endMonthOfChange = (int)$dateTime->format('n');
        $this->endYearOfChange = (int)$dateTime->format('Y');
        if ($useHourAndMin) {
            $this->endHourOfChange = $dateTime->format('H');
            $this->endMinOfChange = $dateTime->format('i');
        }

        return $this;
    }

    /**
     * @param int $paymentsResults Какую информацию по оплатам вернуть.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     * @see \Tmconsulting\Uniteller\Results\PaymentsResults
     *
     */
    public function setPaymentsResults(int $paymentsResults): ResultsBuilder
    {
        $variants = PaymentsResults::toArray();
        if (!in_array($paymentsResults, $variants, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::PAYMENTS_RESULTS . ', must be one of the values: ' . implode(',', $variants)
            );
        }
        $this->paymentsResults = $paymentsResults;

        return $this;
    }

    /**
     * @param int $showPartlyCanceled Будет ли возвращаться в ответе параметр Partly canceled.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setShowPartlyCanceled(int $showPartlyCanceled): ResultsBuilder
    {
        if ($showPartlyCanceled !== 1 && $showPartlyCanceled !== 0) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::SHOW_PARTLY_CANCELED . ', must be one of the values: 1, 0'
            );
        }
        $this->showPartlyCanceled = $showPartlyCanceled;

        return $this;
    }

    /**
     * @param int $zipFlag
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setZipFlag(int $zipFlag): ResultsBuilder
    {
        $variants = ZipFlag::toArray();
        if (!in_array($zipFlag, $variants, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::ZIP_FLAG . ', must be one of the values: ' . implode(',', $variants)
            );
        }
        $this->zipFlag = $zipFlag;

        return $this;
    }

    /**
     * @param int $header Будут ли возвращаться в ответе параметры запроса. 1 - будут, 0 - нет.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setHeader(int $header): ResultsBuilder
    {
        if ($header !== 1 && $header !== 0) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::HEADER . ', must be one of the values: 1, 0'
            );
        }
        $this->header = $header;

        return $this;
    }

    /**
     * @param int $header Будут ли возвращаться в ответе заголовки полей. 1 - будут, 0 - нет.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setHeader1(int $header): ResultsBuilder
    {
        if ($header !== 1 && $header !== 0) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::HEADER1 . ', must be one of the values: 1, 0'
            );
        }
        $this->header1 = $header;

        return $this;
    }

    /**
     * @param string $delimiter Разделитель полей в CVS-формате. Возможные варианты «;», «,», «:», «/».
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setDelimiter(string $delimiter): ResultsBuilder
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @param string $openDelimiter Открывающий разделитель полей в формате «в скобках». Возможные варианты «[», «{», «(».
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setOpenDelimiter(string $openDelimiter): ResultsBuilder
    {
        $this->openDelimiter = $openDelimiter;

        return $this;
    }

    /**
     * @param string $closeDelimiter Закрывающий разделитель полей в формате «в скобках». Возможные варианты «]», «}», «)».
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setCloseDelimiter(string $closeDelimiter): ResultsBuilder
    {
        $this->closeDelimiter = $closeDelimiter;

        return $this;
    }

    /**
     * @param string $rowDelimiter Разделитель строк. Возможные варианты «13», «10», «13,10», «10,13».
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setRowDelimiter(string $rowDelimiter): ResultsBuilder
    {
        $this->rowDelimiter = $rowDelimiter;

        return $this;
    }

    /**
     * @param array $fields Набор информационных полей, возвращаемых в ответе на запрос.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setFields(array $fields): ResultsBuilder
    {
        $this->fields = implode(';', $fields);

        return $this;
    }

    /**
     * @return int
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getFormat(): int
    {
        if (empty($this->format)) {
            throw new RequiredParameterException(NameFieldsUniteller::FORMAT);
        }
        return $this->format;
    }

    /**
     * @return string|null
     */
    public function getOrderIdp(): ?string
    {
        return $this->shopOrderNumber;
    }

    /**
     * @return int|null
     */
    public function getSuccess(): ?int
    {
        return $this->success;
    }

    /**
     * @return int|null
     */
    public function getStartDay(): ?int
    {
        return $this->startDay;
    }

    /**
     * @return int|null
     */
    public function getStartMonth(): ?int
    {
        return $this->startMonth;
    }

    /**
     * @return int|null
     */
    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    /**
     * @return string|null
     */
    public function getStartHour(): ?string
    {
        return $this->startHour;
    }

    /**
     * @return string|null
     */
    public function getStartMin(): ?string
    {
        return $this->startMin;
    }

    /**
     * @return int|null
     */
    public function getEndDay(): ?int
    {
        return $this->endDay;
    }

    /**
     * @return int|null
     */
    public function getEndMonth(): ?int
    {
        return $this->endMonth;
    }

    /**
     * @return int|null
     */
    public function getEndYear(): ?int
    {
        return $this->endYear;
    }

    /**
     * @return string|null
     */
    public function getEndHour(): ?string
    {
        return $this->endHour;
    }

    /**
     * @return string|null
     */
    public function getEndMin(): ?string
    {
        return $this->endMin;
    }

    /**
     * @return int|null
     */
    public function getStartDayOfChange(): ?int
    {
        return $this->startDayOfChange;
    }

    /**
     * @return int|null
     */
    public function getStartMonthOfChange(): ?int
    {
        return $this->startMonthOfChange;
    }

    /**
     * @return int|null
     */
    public function getStartYearOfChange(): ?int
    {
        return $this->startYearOfChange;
    }

    /**
     * @return string|null
     */
    public function getStartHourOfChange(): ?string
    {
        return $this->startHourOfChange;
    }

    /**
     * @return string|null
     */
    public function getStartMinOfChange(): ?string
    {
        return $this->startMinOfChange;
    }

    /**
     * @return int|null
     */
    public function getEndDayOfChange(): ?int
    {
        return $this->endDayOfChange;
    }

    /**
     * @return int|null
     */
    public function getEndMonthOfChange(): ?int
    {
        return $this->endMonthOfChange;
    }

    /**
     * @return int|null
     */
    public function getEndYearOfChange(): ?int
    {
        return $this->endYearOfChange;
    }

    /**
     * @return string|null
     */
    public function getEndHourOfChange(): ?string
    {
        return $this->endHourOfChange;
    }

    /**
     * @return string|null
     */
    public function getEndMinOfChange(): ?string
    {
        return $this->endMinOfChange;
    }

    /**
     * @return int|null
     */
    public function getPaymentsResults(): ?int
    {
        return $this->paymentsResults;
    }

    /**
     * @return int|null
     */
    public function getShowPartlyCanceled(): ?int
    {
        return $this->showPartlyCanceled;
    }

    /**
     * @return int|null
     */
    public function getZipFlag(): ?int
    {
        return $this->zipFlag;
    }

    /**
     * @return int|null
     */
    public function getHeader(): ?int
    {
        return $this->header;
    }

    /**
     * @return int|null
     */
    public function getHeader1(): ?int
    {
        return $this->header1;
    }

    /**
     * @return string|null
     */
    public function getDelimiter(): ?string
    {
        return $this->delimiter;
    }

    /**
     * @return string|null
     */
    public function getOpenDelimiter(): ?string
    {
        return $this->openDelimiter;
    }

    /**
     * @return string|null
     */
    public function getCloseDelimiter(): ?string
    {
        return $this->closeDelimiter;
    }

    /**
     * @return string|null
     */
    public function getRowDelimiter(): ?string
    {
        return $this->rowDelimiter;
    }

    /**
     * @return string|null
     */
    public function getFields(): ?string
    {
        return $this->fields;
    }

    /**
     * @return array
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function toArray(): array
    {
        $arr = [
            NameFieldsUniteller::SHOP_ID  => $this->getShopId(),
            NameFieldsUniteller::LOGIN    => $this->getLogin(),
            NameFieldsUniteller::PASSWORD => $this->getPassword(),
            NameFieldsUniteller::FORMAT   => $this->getFormat(),
        ];
        if ($this->getOrderIdp() !== null) {
            $arr[NameFieldsUniteller::SHOP_ORDER_NUMBER] = $this->getOrderIdp();
        }
        if ($this->getSuccess() !== null) {
            $arr[NameFieldsUniteller::SUCCESS] = $this->getSuccess();
        }
        if ($this->getStartDay() !== null) {
            $arr[NameFieldsUniteller::START_DAY] = $this->getStartDay();
        }
        if ($this->getStartMonth() !== null) {
            $arr[NameFieldsUniteller::START_MONTH] = $this->getStartMonth();
        }
        if ($this->getStartYear() !== null) {
            $arr[NameFieldsUniteller::START_YEAR] = $this->getStartYear();
        }
        if ($this->getStartHour() !== null) {
            $arr[NameFieldsUniteller::START_HOUR] = $this->getStartHour();
        }
        if ($this->getStartMin() !== null) {
            $arr[NameFieldsUniteller::START_MIN] = $this->getStartMin();
        }
        if ($this->getEndDay() !== null) {
            $arr[NameFieldsUniteller::END_DAY] = $this->getEndDay();
        }
        if ($this->getEndMonth() !== null) {
            $arr[NameFieldsUniteller::END_MONTH] = $this->getEndMonth();
        }
        if ($this->getEndYear() !== null) {
            $arr[NameFieldsUniteller::END_YEAR] = $this->getEndYear();
        }
        if ($this->getEndHour() !== null) {
            $arr[NameFieldsUniteller::END_HOUR] = $this->getEndHour();
        }
        if ($this->getEndMin() !== null) {
            $arr[NameFieldsUniteller::END_MIN] = $this->getEndMin();
        }
        if ($this->getStartDayOfChange() !== null) {
            $arr[NameFieldsUniteller::START_DAY_OF_CHANGE] = $this->getStartDayOfChange();
        }
        if ($this->getStartMonthOfChange() !== null) {
            $arr[NameFieldsUniteller::START_MONTH_OF_CHANGE] = $this->getStartMonthOfChange();
        }
        if ($this->getStartYearOfChange() !== null) {
            $arr[NameFieldsUniteller::START_YEAR_OF_CHANGE] = $this->getStartYearOfChange();
        }
        if ($this->getStartHourOfChange() !== null) {
            $arr[NameFieldsUniteller::START_HOUR_OF_CHANGE] = $this->getStartHourOfChange();
        }
        if ($this->getStartMinOfChange() !== null) {
            $arr[NameFieldsUniteller::START_MIN_OF_CHANGE] = $this->getStartMinOfChange();
        }
        if ($this->getEndDayOfChange() !== null) {
            $arr[NameFieldsUniteller::END_DAY_OF_CHANGE] = $this->getEndDayOfChange();
        }
        if ($this->getEndMonthOfChange() !== null) {
            $arr[NameFieldsUniteller::END_MONTH_OF_CHANGE] = $this->getEndMonthOfChange();
        }
        if ($this->getEndYearOfChange() !== null) {
            $arr[NameFieldsUniteller::END_YEAR_OF_CHANGE] = $this->getEndYearOfChange();
        }
        if ($this->getEndHourOfChange() !== null) {
            $arr[NameFieldsUniteller::END_HOUR_OF_CHANGE] = $this->getEndHourOfChange();
        }
        if ($this->getEndMinOfChange() !== null) {
            $arr[NameFieldsUniteller::END_MIN_OF_CHANGE] = $this->getEndMinOfChange();
        }
        if ($this->getMeanType() !== null) {
            $arr[NameFieldsUniteller::MEAN_TYPE] = $this->getMeanType();
        }
        if ($this->getEMoneyType() !== null) {
            $arr[NameFieldsUniteller::E_MONEY_TYPE] = $this->getEMoneyType();
        }
        if ($this->getPaymentsResults() !== null) {
            $arr[NameFieldsUniteller::PAYMENTS_RESULTS] = $this->getPaymentsResults();
        }
        if ($this->getShowPartlyCanceled() !== null) {
            $arr[NameFieldsUniteller::SHOW_PARTLY_CANCELED] = $this->getShowPartlyCanceled();
        }
        if ($this->getZipFlag() !== null) {
            $arr[NameFieldsUniteller::ZIP_FLAG] = $this->getZipFlag();
        }
        if ($this->getHeader() !== null) {
            $arr[NameFieldsUniteller::HEADER] = $this->getHeader();
        }
        if ($this->getHeader1() !== null) {
            $arr[NameFieldsUniteller::HEADER1] = $this->getHeader1();
        }
        if ($this->getDelimiter() !== null) {
            $arr[NameFieldsUniteller::DELIMITER] = $this->getDelimiter();
        }
        if ($this->getOpenDelimiter() !== null) {
            $arr[NameFieldsUniteller::OPEN_DELIMITER] = $this->getOpenDelimiter();
        }
        if ($this->getCloseDelimiter() !== null) {
            $arr[NameFieldsUniteller::CLOSE_DELIMITER] = $this->getCloseDelimiter();
        }
        if ($this->getRowDelimiter() !== null) {
            $arr[NameFieldsUniteller::ROW_DELIMITER] = $this->getRowDelimiter();
        }
        if ($this->getFields() !== null) {
            $arr[NameFieldsUniteller::S_FIELDS] = $this->getFields();
        }
        return $arr;
    }

    /**
     * Создает объект ResultsBuilder и наполняет из входящего массива параметров
     *
     * @param array $parameters Массив с параметрами
     *
     * @return \Tmconsulting\Uniteller\Builder\BuilderInterface
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public static function setFromArray(array $parameters): BuilderInterface
    {
        $builder = new static();
        if (!empty($parameters[NameFieldsUniteller::BASE_URI])) {
            $builder->setBaseUri($parameters[NameFieldsUniteller::BASE_URI]);
        }
        if (!empty($parameters[NameFieldsUniteller::SHOP_ID])) {
            $builder->setShopId($parameters[NameFieldsUniteller::SHOP_ID]);
        }
        if (!empty($parameters[NameFieldsUniteller::LOGIN])) {
            $builder->setLogin($parameters[NameFieldsUniteller::LOGIN]);
        }
        if (!empty($parameters[NameFieldsUniteller::PASSWORD])) {
            $builder->setPassword($parameters[NameFieldsUniteller::PASSWORD]);
        }
        if (!empty($parameters[NameFieldsUniteller::SHOP_ORDER_NUMBER])) {
            $builder->setOrderIdp($parameters[NameFieldsUniteller::SHOP_ORDER_NUMBER]);
        }
        if (!empty($parameters[NameFieldsUniteller::MEAN_TYPE])) {
            $builder->setMeanType($parameters[NameFieldsUniteller::MEAN_TYPE]);
        }
        if (!empty($parameters[NameFieldsUniteller::E_MONEY_TYPE])) {
            $builder->setEMoneyType($parameters[NameFieldsUniteller::E_MONEY_TYPE]);
        }
        if (!empty($parameters[NameFieldsUniteller::FORMAT])) {
            $builder->setFormat($parameters[NameFieldsUniteller::FORMAT]);
        }
        if (!empty($parameters[NameFieldsUniteller::SUCCESS])) {
            $builder->setSuccess($parameters[NameFieldsUniteller::SUCCESS]);
        }
        if (!empty($parameters['start'])) {
            $builder->setStart($parameters['start']);
        }
        if (!empty($parameters['startOfChange'])) {
            $builder->setStartOfChange($parameters['startOfChange']);
        }
        if (!empty($parameters['end'])) {
            $builder->setEnd($parameters['end']);
        }
        if (!empty($parameters['endOfChange'])) {
            $builder->setEndOfChange($parameters['endOfChange']);
        }
        if (!empty($parameters[NameFieldsUniteller::PAYMENTS_RESULTS])) {
            $builder->setPaymentsResults($parameters[NameFieldsUniteller::PAYMENTS_RESULTS]);
        }
        if (!empty($parameters[NameFieldsUniteller::SHOW_PARTLY_CANCELED])) {
            $builder->setShowPartlyCanceled($parameters[NameFieldsUniteller::SHOW_PARTLY_CANCELED]);
        }
        if (!empty($parameters[NameFieldsUniteller::ZIP_FLAG])) {
            $builder->setZipFlag($parameters[NameFieldsUniteller::ZIP_FLAG]);
        }
        if (!empty($parameters[NameFieldsUniteller::HEADER])) {
            $builder->setHeader($parameters[NameFieldsUniteller::HEADER]);
        }
        if (!empty($parameters[NameFieldsUniteller::HEADER1])) {
            $builder->setHeader1($parameters[NameFieldsUniteller::HEADER1]);
        }
        if (!empty($parameters[NameFieldsUniteller::DELIMITER])) {
            $builder->setDelimiter($parameters[NameFieldsUniteller::DELIMITER]);
        }
        if (!empty($parameters[NameFieldsUniteller::OPEN_DELIMITER])) {
            $builder->setOpenDelimiter($parameters[NameFieldsUniteller::OPEN_DELIMITER]);
        }
        if (!empty($parameters[NameFieldsUniteller::CLOSE_DELIMITER])) {
            $builder->setCloseDelimiter($parameters[NameFieldsUniteller::CLOSE_DELIMITER]);
        }
        if (!empty($parameters[NameFieldsUniteller::ROW_DELIMITER])) {
            $builder->setRowDelimiter($parameters[NameFieldsUniteller::ROW_DELIMITER]);
        }
        if (!empty($parameters[NameFieldsUniteller::S_FIELDS])) {
            $builder->setFields($parameters[NameFieldsUniteller::S_FIELDS]);
        }
        return $builder;
    }
}
