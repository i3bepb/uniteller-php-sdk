<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\ArraybleInterface;
use Tmconsulting\Uniteller\Common\Builder;
use Tmconsulting\Uniteller\Common\NameFieldsUniteller;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Payment\EMoneyType;
use Tmconsulting\Uniteller\Payment\MeanType;

class ResultsBuilder implements ArraybleInterface, Builder
{
    /**
     * Shop_IDP (Client Point ID)
     * Текст, содержащий латинские буквы, цифры и "-"
     *
     * @var string
     */
    private $shopIdp;

    /**
     * Логин. Доступен Мерчанту в Личном кабинете, пункт меню «Параметры Авторизации».
     *
     * @var string
     */
    private $login;

    /**
     * Пароль. Доступен Мерчанту в Личном кабинете, пункт меню «Параметры Авторизации».
     *
     * @var string
     */
    private $password;

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
    private $shopOrderNumber = '';

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
     * Платёжная система кредитной карты.
     * Может принимать значения: 0 — любая, 1 — VISA, 2 — MasterCard,
     * 3 — Diners Club, 4 — JCB, 5 — American Express.
     *
     * @see \Tmconsulting\Uniteller\Payment\MeanType
     *
     * @var int
     */
    private $meanType;

    /**
     * Тип электронной валюты.
     * 0 - Любая система электронных платежей
     * 1 - Яндекс.Деньги
     * 13 - Оплата наличными (Евросеть, Яндекс.Деньги и пр.)
     * 18 - QIWI Кошелек REST (по протоколу REST)
     * 29 - WebMoney WMR
     *
     * @see \Tmconsulting\Uniteller\Payment\EMoneyType
     *
     * @var int
     */
    private $eMoneyType;

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
     * @param string $shopIdp
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setShopIdp(string $shopIdp): ResultsBuilder
    {
        $this->shopIdp = $shopIdp;

        return $this;
    }

    /**
     * @param string $login
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setLogin(string $login): ResultsBuilder
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setPassword(string $password): ResultsBuilder
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param int $format
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
     * @param int $success
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setSuccess(int $success): ResultsBuilder
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setStart(\DateTime $dateTime): ResultsBuilder
    {
        $this->startDay = (int)$dateTime->format('j');
        $this->startMonth = (int)$dateTime->format('n');
        $this->startYear = (int)$dateTime->format('Y');
        $this->startHour = $dateTime->format('H');
        $this->startMin = $dateTime->format('i');

        return $this;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setEnd(\DateTime $dateTime): ResultsBuilder
    {
        $this->endDay = (int)$dateTime->format('j');
        $this->endMonth = (int)$dateTime->format('n');
        $this->endYear = (int)$dateTime->format('Y');
        $this->endHour = $dateTime->format('H');
        $this->endMin = $dateTime->format('i');

        return $this;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setStartOfChange(\DateTime $dateTime): ResultsBuilder
    {
        $this->startDayOfChange = (int)$dateTime->format('j');
        $this->startMonthOfChange = (int)$dateTime->format('n');
        $this->startYearOfChange = (int)$dateTime->format('Y');
        $this->startHourOfChange = $dateTime->format('H');
        $this->startMinOfChange = $dateTime->format('i');

        return $this;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setEndOfChange(\DateTime $dateTime): ResultsBuilder
    {
        $this->endDayOfChange = (int)$dateTime->format('j');
        $this->endMonthOfChange = (int)$dateTime->format('n');
        $this->endYearOfChange = (int)$dateTime->format('Y');
        $this->endHourOfChange = $dateTime->format('H');
        $this->endMinOfChange = $dateTime->format('i');

        return $this;
    }

    /**
     * @param int $meanType
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setMeanType(int $meanType): ResultsBuilder
    {
        $types = MeanType::toArray();
        if (!in_array($meanType, $types, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::MEAN_TYPE . ', must be one of the values: ' . implode(',', $types)
            );
        }
        $this->meanType = $meanType;

        return $this;
    }

    /**
     * @param int $eMoneyType
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setEMoneyType(int $eMoneyType): ResultsBuilder
    {
        $types = EMoneyType::toArray();
        if (!in_array($eMoneyType, $types, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::E_MONEY_TYPE . ', must be one of the values: ' . implode(',', $types)
            );
        }
        $this->eMoneyType = $eMoneyType;

        return $this;
    }

    /**
     * @param int $paymentsResults
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
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
     * @param int $showPartlyCanceled
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setShowPartlyCanceled(int $showPartlyCanceled): ResultsBuilder
    {
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
     * @param int $header
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setHeader(int $header): ResultsBuilder
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @param int $header
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setHeader1(int $header): ResultsBuilder
    {
        $this->header1 = $header;

        return $this;
    }

    /**
     * @param string $delimiter Разделитель полей в CVS-формате.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setDelimiter(string $delimiter): ResultsBuilder
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @param string $openDelimiter Открывающий разделитель полей в формате «в скобках».
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setOpenDelimiter(string $openDelimiter): ResultsBuilder
    {
        $this->openDelimiter = $openDelimiter;

        return $this;
    }

    /**
     * @param string $closeDelimiter Закрывающий разделитель полей в формате «в скобках».
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     */
    public function setCloseDelimiter(string $closeDelimiter): ResultsBuilder
    {
        $this->closeDelimiter = $closeDelimiter;

        return $this;
    }

    /**
     * @param string $rowDelimiter Разделитель строк.
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
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getShopIdp(): string
    {
        if (empty($this->shopIdp)) {
            throw new RequiredParameterException(NameFieldsUniteller::SHOP_ID);
        }

        return $this->shopIdp;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getLogin(): string
    {
        if (empty($this->login)) {
            throw new RequiredParameterException(NameFieldsUniteller::PASSWORD);
        }
        return $this->login;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getPassword(): string
    {
        if (empty($this->password)) {
            throw new RequiredParameterException(NameFieldsUniteller::LOGIN);
        }
        return $this->password;
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
     * @return string
     */
    public function getOrderIdp(): string
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
    public function getMeanType(): ?int
    {
        return $this->meanType;
    }

    /**
     * @return int|null
     */
    public function getEMoneyType(): ?int
    {
        return $this->eMoneyType;
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

    public function toArray(): array
    {
        $arr = [
            NameFieldsUniteller::SHOP_ID           => $this->getShopIdp(),
            NameFieldsUniteller::SHOP_ORDER_NUMBER => $this->getOrderIdp(),
        ];
        return $arr;
    }
}