<?php

namespace Tmconsulting\Uniteller\Confirm;

use Tmconsulting\Uniteller\Builder\BaseBuilder;
use Tmconsulting\Uniteller\Dependency\ContainerAwareInterface;
use Tmconsulting\Uniteller\Dependency\ContainerAwareTrait;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\AmountParameter;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\Currency;
use Tmconsulting\Uniteller\Parameter\Enum\Language;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\EnumParameter;
use Tmconsulting\Uniteller\Parameter\FormatParameter;
use Tmconsulting\Uniteller\Parameter\ScalarParameter;
use Tmconsulting\Uniteller\Parameter\SFieldsParameter;
use Tmconsulting\Uniteller\Parameter\StringParameter;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\RequestManager;

/**
 * Подтверждение без фискализации.
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
 * @method $this setBillNumber(string|int $billNumber) Номер платежа в системе Uniteller (RRN). 12 цифр.
 * @method string|null getBillNumber()
 * @method bool hasBillNumber()
 *
 * @method $this setShopId(string|int $shopId) Идентификатор точки продажи в системе Uniteller.
 * @method string|null getShopId()
 * @method bool hasShopId()
 *
 * @method $this setSubtotal(string|int $subtotal) Измененная сумма транзакции.
 * @method string|null getSubtotal()
 * @method bool hasSubtotal()
 *
 * @method $this setCurrency(string $currency) Валюта.
 * @method string|null getCurrency()
 * @method bool hasCurrency()
 *
 * @method $this setLanguage(string $language) Код языка.
 * @method string|null getLanguage()
 * @method bool hasLanguage()
 *
 * @method $this setSFields(array $fields)
 * @method string|null getSFields()
 * @method bool hasSFields()
 */
class ConfirmBuilder extends BaseBuilder implements ContainerAwareInterface
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
        // Формат ответа.
        $this->parameters->add(
            new FormatParameter(
                CanonicalParameterName::FORMAT,
                UnitellerParameterName::FORMAT,
                Format::getSupportedForEndpoint($this->getEndpoint()),
                $this->getEndpoint()
            )
        );
        // Номер платежа в системе Uniteller (RRN). 12 цифр.
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::BILLNUMBER, UnitellerParameterName::BILLNUMBER, 12)
        );
        // Идентификатор точки продажи в системе Uniteller.
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::SHOP_ID, UnitellerParameterName::SHOP_ID)
        );
        /**
         * Измененная сумма транзакции. Должна быть не более суммы исходного платежа.
         * Передается только в случае необходимости изменения суммы платежа.
         */
        $this->parameters->add(
            new AmountParameter(CanonicalParameterName::SUBTOTAL, UnitellerParameterName::SUBTOTAL_P)
        );
        /**
         * Валюта платежа. Одно из \Tmconsulting\Uniteller\Parameter\Enum\Currency.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\Currency
         */
        $this->parameters->add(
            new EnumParameter(CanonicalParameterName::CURRENCY, UnitellerParameterName::CURRENCY, Currency::toArray())
        );
        /**
         * Код языка интерфейса платёжной страницы (2 символа).
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\Language
         */
        $this->parameters->add(
            new EnumParameter(CanonicalParameterName::LANGUAGE, UnitellerParameterName::LANGUAGE, Language::toArray())
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
        if (!$this->hasBillNumber()) {
            throw new RequiredParameterException(UnitellerParameterName::BILLNUMBER);
        }
        if (!$this->hasShopId()) {
            throw new RequiredParameterException(UnitellerParameterName::SHOP_ID);
        }
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return ApiEndpoints::CONFIRM;
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

    public function process()
    {
        $this->container->set(ParserInterface::class, Format::getParserByFormat($this->getResponseFormat()));
        $requestManager = $this->container->get(RequestManager::class);

        if ($this->debug) {
            $this->logger->debug('Parameters in request: ' . PHP_EOL . print_r($this->toArray(), true));
        }

        try {
            $result = $requestManager->executeRequestAndParseResponseOrders($this);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            throw $e;
        }

        return $result;
    }
}
