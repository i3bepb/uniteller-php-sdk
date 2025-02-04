<?php

namespace Tmconsulting\Uniteller\Cancel;

use Tmconsulting\Uniteller\Builder\BaseBuilder;
use Tmconsulting\Uniteller\Dependency\ContainerAwareInterface;
use Tmconsulting\Uniteller\Dependency\ContainerAwareTrait;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\AmountParameter;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\ReceiptParameter;
use Tmconsulting\Uniteller\Parameter\ScalarParameter;
use Tmconsulting\Uniteller\Parameter\StringParameter;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\RequestManager;

/**
 * Сценарии отмены.
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
 * @method $this setSubtotal(string|int $subtotal) Отменяемая сумма оплаты электронными денежными средствами.
 * @method string|null getSubtotal()
 * @method bool hasSubtotal()
 *
 * @method $this setReceipt(\Tmconsulting\Uniteller\Receipt\Receipt $receipt) Чек
 * @method bool|null getReceipt()
 * @method bool hasReceipt()
 */
class CancelBuilder extends BaseBuilder implements ContainerAwareInterface
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
         */
        $this->parameters->add(
            new AmountParameter(CanonicalParameterName::SUBTOTAL, UnitellerParameterName::SUBTOTAL)
        );
        // Чек
        $this->parameters->add(
            new ReceiptParameter(CanonicalParameterName::RECEIPT, UnitellerParameterName::RECEIPT)
        );
    }

    /**
     * Порядок следования полей важен т.к. иначе signature может не пройти!
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
            UnitellerParameterName::SUBTOTAL => $this->getSubtotal(),
            UnitellerParameterName::RECEIPT  => $this->getReceipt(),
            UnitellerParameterName::PASSWORD => $this->getPassword(),
        ];
        return $this->signatureCreator->setParameters($arr)->createSha256();
    }

    /**
     * Проверка присутствия обязательных полей
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
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
        if (!$this->hasSubtotal()) {
            throw new RequiredParameterException(UnitellerParameterName::SUBTOTAL);
        }
        if (!$this->hasReceipt()) {
            throw new RequiredParameterException(UnitellerParameterName::RECEIPT);
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
