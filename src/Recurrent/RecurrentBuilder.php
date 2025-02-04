<?php

namespace Tmconsulting\Uniteller\Recurrent;

use Tmconsulting\Uniteller\Builder\BaseBuilder;
use Tmconsulting\Uniteller\Dependency\ContainerAwareInterface;
use Tmconsulting\Uniteller\Dependency\ContainerAwareTrait;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\AmountParameter;
use Tmconsulting\Uniteller\Parameter\Enum\CallbackFormat;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\EnumParameter;
use Tmconsulting\Uniteller\Parameter\ScalarParameter;
use Tmconsulting\Uniteller\Parameter\StringParameter;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\RequestManager;

/**
 * Сценарии рекуррентного платежа.
 *
 * @method $this setShopId(string|int $shopId) Идентификатор точки продажи в системе Uniteller.
 * @method string|null getShopId()
 * @method bool hasShopId()
 *
 * @method $this setParentShopId(string|int $shopId) Идентификатор точки продажи «родительского» платёжа.
 * @method string|null getParentShopId()
 * @method bool hasParentShopId()
 *
 * @method $this setPassword(string|int $password) Пароль. Доступен Merchant-у в Личном кабинете.
 * @method string|null getPassword()
 * @method bool hasPassword()
 *
 * @method $this setLogin(string|int $login) Логин. Доступен Merchant-у в Личном кабинете.
 * @method string|null getLogin()
 * @method bool hasLogin()
 *
 * @method $this setOrderId(string|int $orderId) Идентификатор заказа в системе Merchant, соответствующий данному платежу.
 * @method string|null getOrderId()
 * @method bool hasOrderId()
 *
 * @method $this setParentOrderId(string|int $shopId) Идентификатор заказа в системе Merchant «родительского» платежа.
 * @method string|null getParentOrderId()
 * @method bool hasParentOrderId()
 *
 * @method $this setSubtotal(string|int $subtotal) Сумма текущего рекуррентного платежа.
 * @method string|null getSubtotal()
 * @method bool hasSubtotal()
 *
 * @method $this setCustomerId(string|int $customerId) Идентификатор покупателя, используемый некоторыми интернет-магазинами, до 64 символов
 * @method string|null getCustomerId()
 * @method bool hasCustomerId()
 *
 * @method $this setCallbackFormat(string $callbackFormat) Запрашиваемый формат уведомления о статусе оплаты
 * @method string|null getCallbackFormat()
 * @method bool hasCallbackFormat()
 */
class RecurrentBuilder extends BaseBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return void
     */
    protected function registerParameters(): void
    {
        // Логин. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::LOGIN, UnitellerParameterName::LOGIN, null, false)
        );
        // Пароль. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::PASSWORD, UnitellerParameterName::PASSWORD, null, false)
        );
        // Идентификатор точки продажи в системе Uniteller.
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::SHOP_ID, UnitellerParameterName::SHOP_IDP)
        );
        // Идентификатор точки продажи в системе Uniteller, через которую был проведён «родительский» платёж.
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::PARENT_SHOP_ID, UnitellerParameterName::PARENT_SHOP_IDP)
        );
        /**
         * Идентификатор заказа в системе Merchant, соответствующий данному платежу. Может быть любой непустой строкой
         * максимальной длиной 127 символов, не может содержать только пробелы.
         */
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::ORDER_ID, UnitellerParameterName::ORDER_IDP, 127)
        );
        /**
         * Номер (Order_IDP) «родительского» платежа в системе расчётов интернет-магазина.
         * Может быть любой непустой строкой максимальной длиной 127 символов, не может содержать только пробелы.
         */
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::PARENT_ORDER_ID, UnitellerParameterName::PARENT_ORDER_IDP, 127)
        );
        /**
         * Сумма текущего рекуррентного платежа.
         * В качестве десятичного разделителя используется точка, не более 2 знаков после разделителя. Например, 12.34
         */
        $this->parameters->add(
            new AmountParameter(CanonicalParameterName::SUBTOTAL, UnitellerParameterName::SUBTOTAL_P)
        );
        // Идентификатор Покупателя, используемый некоторыми интернет-магазинами (до 64 символов).
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::CUSTOMER_ID, UnitellerParameterName::CUSTOMER_IDP, 64)
        );
        // Запрашиваемый формат уведомления о статусе оплаты.
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::CALLBACK_FORMAT,
                UnitellerParameterName::CALLBACK_FORMAT,
                CallbackFormat::toArray()
            )
        );
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
        if (!$this->hasShopId()) {
            throw new RequiredParameterException(UnitellerParameterName::SHOP_IDP);
        }
        if (!$this->hasOrderId()) {
            throw new RequiredParameterException(UnitellerParameterName::ORDER_IDP);
        }
        if (!$this->hasParentOrderId()) {
            throw new RequiredParameterException(UnitellerParameterName::PARENT_ORDER_IDP);
        }
        if (!$this->hasSubtotal()) {
            throw new RequiredParameterException(UnitellerParameterName::SUBTOTAL_P);
        }
    }

    /**
     * Порядок следования полей важен т.к. иначе signature может не пройти!
     *
     * @return string Сигнатура.
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getSignature(): string
    {
        $arr = [
            UnitellerParameterName::SHOP_IDP => $this->getShopId(),
        ];
        if ($this->hasParentShopId()) {
            $arr[UnitellerParameterName::PARENT_SHOP_IDP] = $this->getParentShopId();
        }
        $arr[UnitellerParameterName::ORDER_IDP] = $this->getOrderId();
        $arr[UnitellerParameterName::SUBTOTAL_P] = $this->getSubtotal();
        $arr[UnitellerParameterName::PARENT_ORDER_IDP] = $this->getParentOrderId();
        $arr[UnitellerParameterName::PASSWORD] = $this->getPassword();

        return $this->signatureCreator->setParameters($arr)->createMd5();
    }

    /**
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
     * Return request name.
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return ApiEndpoints::RECURRENT;
    }

    /**
     * @return \Tmconsulting\Uniteller\Order\Order[]
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws \Throwable
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

    /**
     * @return string|null Формат ответа.
     */
    public function getResponseFormat(): ?string
    {
        return Format::CSV;
    }
}
