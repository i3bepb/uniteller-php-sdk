<?php

namespace Tmconsulting\Uniteller\Cancel;

use Tmconsulting\Uniteller\Builder\BaseBuilder;
use Tmconsulting\Uniteller\Dependency\ContainerAwareInterface;
use Tmconsulting\Uniteller\Dependency\ContainerAwareTrait;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\NonNegativeAmountParameter;
use Tmconsulting\Uniteller\Parameter\ReceiptParameter;
use Tmconsulting\Uniteller\Parameter\ScalarParameter;
use Tmconsulting\Uniteller\Parameter\StringParameter;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Support\ExceptionContext;

/**
 * Отмена платежа через API фискализации с ответом в формате JSON.
 *
 * При отмене неподтверждённой преавторизации без чека аванса Receipt не передаётся.
 *
 * @method $this setPassword(string|int $password) Пароль. Доступен Merchant-у в Личном кабинете.
 * @method string|null getPassword()
 * @method bool hasPassword()
 *
 * @method $this setShopId(string|int $shopId) Идентификатор точки продажи в системе Uniteller.
 * @method string|null getShopId()
 * @method bool hasShopId()
 *
 * @method $this setOrderId(string|int $orderId) Идентификатор заказа в системе Merchant.
 * @method string|null getOrderId()
 * @method bool hasOrderId()
 *
 * @method $this setSubtotal(string|int $subtotal) Отменяемая сумма электронной оплаты; ноль допустим только с Receipt.
 * @method string|null getSubtotal()
 * @method bool hasSubtotal()
 *
 * @method $this setReceipt(\Tmconsulting\Uniteller\Receipt\Receipt $receipt) Чек возврата.
 * @method string|null getReceipt() Чек, закодированный методом Base64.
 * @method bool hasReceipt()
 */
class FiscalCancelBuilder extends BaseBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return void
     */
    protected function registerParameters(): void
    {
        // Пароль. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::PASSWORD, UnitellerParameterName::PASSWORD, null, false)
        );
        /**
         * Идентификатор заказа в системе Merchant, соответствующий данному платежу. Может быть любой непустой строкой
         * максимальной длиной 127 символов, не может содержать только пробелы.
         */
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::ORDER_ID, UnitellerParameterName::ORDER_ID, 127)
        );
        // Идентификатор точки продажи в системе Uniteller.
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::SHOP_ID, UnitellerParameterName::UPID)
        );
        /**
         * Отменяемая сумма оплаты электронными денежными средствами.
         * Ноль допустим при передаче Receipt для отмены только ДПС.
         */
        $this->parameters->add(
            new NonNegativeAmountParameter(CanonicalParameterName::SUBTOTAL, UnitellerParameterName::SUBTOTAL)
        );
        // Чек
        $this->parameters->add(
            new ReceiptParameter(CanonicalParameterName::RECEIPT, UnitellerParameterName::RECEIPT)
        );
    }

    /**
     * Порядок следования полей важен т.к. иначе signature может не пройти!
     * Subtotal и Receipt включаются только при наличии соответствующего параметра.
     * При полной отмене с чеком Subtotal не передаётся. Для отсутствующего поля в подписываемую строку
     * не добавляются ни хеш, ни разделитель.
     *
     * @return string Сигнатура.
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     * @throws \Tmconsulting\Uniteller\Exception\EndpointNotSupportedException
     */
    public function getSignature(): string
    {
        $arr = [
            UnitellerParameterName::ORDER_ID => $this->getOrderId(),
            UnitellerParameterName::UPID     => $this->getShopId(),
        ];
        if ($this->hasSubtotal()) {
            $arr[UnitellerParameterName::SUBTOTAL] = $this->getSubtotal();
        }
        if ($this->hasReceipt()) {
            $arr[UnitellerParameterName::RECEIPT] = $this->getReceipt();
        }
        $arr[UnitellerParameterName::PASSWORD] = $this->getPassword();

        return $this->signatureCreator->setParameters($arr)->createSha256();
    }

    /**
     * Проверяет обязательные поля и сочетание суммы с чеком возврата.
     *
     * Без Receipt требуется положительный Subtotal. С Receipt сумма может
     * отсутствовать для полной отмены или быть нулевой для отмены только ДПС.
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException Если Subtotal равен нулю без Receipt.
     */
    protected function validateRequired(): void
    {
        if (!$this->hasPassword()) {
            throw new RequiredParameterException(UnitellerParameterName::PASSWORD);
        }
        if (!$this->hasShopId()) {
            throw new RequiredParameterException(UnitellerParameterName::UPID);
        }
        if (!$this->hasOrderId()) {
            throw new RequiredParameterException(UnitellerParameterName::ORDER_IDP);
        }
        // При полной отмене с чеком сумма определяется по Receipt.
        if (!$this->hasSubtotal() && !$this->hasReceipt()) {
            throw new RequiredParameterException(UnitellerParameterName::SUBTOTAL);
        }
        if (!$this->hasReceipt() && $this->getSubtotal() <= 0) {
            throw new NotValidParameterException('Invalid Subtotal: amount must be greater than zero without Receipt.');
        }
    }

    /**
     * Возвращает массив со значениями параметров запроса.
     *
     * @return array
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr[UnitellerParameterName::SIGNATURE] = $this->getSignature();

        return $arr;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return ApiEndpoints::FISCAL_CANCEL;
    }

    /**
     * @return string|null Формат ответа.
     */
    public function getResponseFormat(): ?string
    {
        return Format::JSON;
    }

    /**
     * Отправляет отмену и разбирает JSON-ответ с проверкой кода ошибки и декодированием чеков.
     *
     * @return array Поля ответа; непустое поле Receipt заменяется массивом FiscalReceipt.
     *
     * @throws \Throwable
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws \Tmconsulting\Uniteller\Exception\FormatNotSupportedException
     */
    public function process()
    {
        $this->container->set(ParserInterface::class, Format::getParserByFormat($this->getResponseFormat()));
        $requestManager = $this->container->get(RequestManager::class);

        if ($this->debug) {
            $this->logger->debug(
                'uniteller.cancel.request_prepared',
                ['uniteller' => ['parameters' => $this->toArray()]]
            );
        }

        try {
            $response = $requestManager->executeRequest($this);
            $result = $this->container->get(CancelResultParser::class)->parse($response);
        } catch (\Throwable $e) {
            $this->logger->error(
                'uniteller.cancel.failed',
                [
                    'uniteller' => [
                        'error' => ExceptionContext::fromThrowable($e),
                        'message' => ExceptionContext::logMessage($e, 'Uniteller operation failed'),
                    ]
                ]
            );
            throw $e;
        }

        return $result;
    }
}
