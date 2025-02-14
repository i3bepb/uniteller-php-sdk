<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller;

use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Tmconsulting\Uniteller\Cancel\CancelRequest;
use Tmconsulting\Uniteller\Common\GetParametersFromBuilder;
use Tmconsulting\Uniteller\Common\NameFieldsUniteller;
use Tmconsulting\Uniteller\Dependency\ContainerAwareInterface;
use Tmconsulting\Uniteller\Dependency\ContainerAwareTrait;
use Tmconsulting\Uniteller\Exception\NotImplementedException;
use Tmconsulting\Uniteller\Http\HttpManager;
use Tmconsulting\Uniteller\Http\HttpManagerInterface;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Payment\Payment;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Payment\Uri;
use Tmconsulting\Uniteller\Recurrent\RecurrentRequest;
use Tmconsulting\Uniteller\Request\RequestInterface;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Results\ResultsRequest;
use Tmconsulting\Uniteller\Signature\SignatureCallback;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Signature\SignaturePayment;
use Tmconsulting\Uniteller\Signature\SignatureRecurrent;

/**
 * Class Client
 *
 * @package Tmconsulting\Uniteller
 */
class Client implements ClientInterface, ContainerAwareInterface, LoggerAwareInterface
{
    use GetParametersFromBuilder;
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var \Tmconsulting\Uniteller\Dependency\UnitellerContainer
     */
    protected $container;

    /**
     * @var string
     */
    protected $baseUri = 'https://wpay.uniteller.ru';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var SignatureInterface
     */
    protected $signatureRecurrent;

    /**
     * @var SignatureInterface
     */
    protected $signatureCallback;

    /**
     * @var RequestInterface
     */
    protected $cancelRequest;

    /**
     * @var RequestInterface
     */
    protected $recurrentRequest;

    /**
     * @var HttpManagerInterface
     */
    protected $httpManager;

    /**
     * Client constructor.
     */
    public function __construct()
    {
        $this->setHttpManager(new HttpManager(new GuzzleAdapter(new GuzzleClient()), ['base_uri' => $this->getBaseUri()]));
        $this->registerCancelRequest(new CancelRequest());
        $this->registerRecurrentRequest(new RecurrentRequest());
        $this->registerSignatureRecurrent(new SignatureRecurrent());
        $this->registerSignatureCallback(new SignatureCallback());
    }

    /**
     * @param string $uri
     *
     * @return \Tmconsulting\Uniteller\Client
     */
    public function setBaseUri(string $uri): Client
    {
        $this->baseUri = $uri;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return \Tmconsulting\Uniteller\Client
     */
    public function setOptions(array $options): Client
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @param \Tmconsulting\Uniteller\Http\HttpManagerInterface $httpManager
     *
     * @return \Tmconsulting\Uniteller\Client
     */
    public function setHttpManager(HttpManagerInterface $httpManager): Client
    {
        $this->httpManager = $httpManager;

        return $this;
    }

    /**
     * @param \Tmconsulting\Uniteller\Request\RequestInterface $cancel
     *
     * @return \Tmconsulting\Uniteller\Client
     */
    public function registerCancelRequest(RequestInterface $cancel): Client
    {
        $this->cancelRequest = $cancel;

        return $this;
    }

    /**
     * @param \Tmconsulting\Uniteller\Request\RequestInterface $request
     *
     * @return \Tmconsulting\Uniteller\Client
     */
    public function registerRecurrentRequest(RequestInterface $request): Client
    {
        $this->recurrentRequest = $request;

        return $this;
    }

    /**
     * @param \Tmconsulting\Uniteller\Signature\SignatureInterface $signature
     *
     * @return \Tmconsulting\Uniteller\Client
     */
    public function registerSignatureRecurrent(SignatureInterface $signature): Client
    {
        $this->signatureRecurrent = $signature;

        return $this;
    }

    /**
     * @param \Tmconsulting\Uniteller\Signature\SignatureInterface $signature
     *
     * @return \Tmconsulting\Uniteller\Client
     */
    public function registerSignatureCallback(SignatureInterface $signature): Client
    {
        $this->signatureCallback = $signature;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption(string $key, $default = null)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        return $default;
    }

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @return \Tmconsulting\Uniteller\Request\RequestInterface
     */
    public function getCancelRequest()
    {
        return $this->cancelRequest;
    }

    /**
     * @return \Tmconsulting\Uniteller\Request\RequestInterface
     */
    public function getRecurrentRequest()
    {
        return $this->recurrentRequest;
    }

    /**
     * @return \Tmconsulting\Uniteller\Signature\SignatureInterface
     */
    public function getSignatureRecurrent()
    {
        return $this->signatureRecurrent;
    }

    /**
     * @return \Tmconsulting\Uniteller\Signature\SignatureInterface
     */
    public function getSignatureCallback()
    {
        return $this->signatureCallback;
    }

    /**
     * @return \Tmconsulting\Uniteller\Http\HttpManagerInterface
     */
    public function getHttpManager()
    {
        return $this->httpManager;
    }

    /**
     * Получение платежной ссылки или сразу переход к оплате.
     *
     * @param \Tmconsulting\Uniteller\Payment\PaymentBuilder|array $paymentBuilder
     *
     * @return \Tmconsulting\Uniteller\Payment\UriInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function payment($paymentBuilder): Uri
    {
        if (is_array($paymentBuilder)) {
            $paymentBuilder = PaymentBuilder::setFromArray($paymentBuilder);
        }
        $parameters = $paymentBuilder->toArray();
        $parameters[NameFieldsUniteller::SIGNATURE] = $this->container->get(SignaturePayment::class)->create($paymentBuilder);

        if ($this->logger) {
            $this->logger->debug('Parameters in request: ' . PHP_EOL . print_r($parameters, true));
        }
        return $this->container->get(Payment::class)->execute($parameters, ['base_uri' => $paymentBuilder->getBaseUri()]);
    }

    /**
     * Отмена платежа.
     *
     * @param \Tmconsulting\Uniteller\Cancel\CancelBuilder|array $parameters
     * @return mixed
     * @internal param  $builder
     */
    public function cancel($parameters)
    {
        return $this->callRequestFor('cancel', $parameters);
    }

    /**
     * @param \Tmconsulting\Uniteller\Results\ResultsBuilder|array $resultsBuilder
     *
     * @return Order
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function results($resultsBuilder)
    {
        if (is_array($resultsBuilder)) {
            $resultsBuilder = ResultsBuilder::setFromArray($resultsBuilder);
        }
        $request = $this->container->get(ResultsRequest::class);

        return $request->execute(
            $this->getHttpManager(),
            $resultsBuilder->toArray()
        );
    }

    /**
     * @param \Tmconsulting\Uniteller\Recurrent\RecurrentBuilder|array $parameters
     * @return mixed
     * @throws \Tmconsulting\Uniteller\Exception\NotImplementedException
     */
    public function recurrent($parameters)
    {
        $array = $this->getParameters($parameters);
        $array['Shop_IDP'] = $this->getShopId();

        $this->signatureRecurrent
            ->setShopIdp(array_get($array, 'Shop_IDP'))
            ->setOrderIdp(array_get($array, 'Order_IDP'))
            ->setSubtotalP(array_get($array, 'Subtotal_P'))
            ->setParentOrderIdp(array_get($array, 'Parent_Order_IDP'))
            ->setPassword($this->getPassword());
        if (array_get($array, 'Parent_Shop_IDP')) {
            $this->signatureRecurrent->setParentShopIdp(array_get($array, 'Parent_Shop_IDP'));
        }

        $array['Signature'] = $this->signatureRecurrent->create();

        return $this->callRequestFor('recurrent', $array);
    }

    /**
     * @param array $parameters
     * @return mixed
     * @throws \Tmconsulting\Uniteller\Exception\NotImplementedException
     */
    public function confirm($parameters)
    {
        throw new NotImplementedException(sprintf(
            'In current moment, feature [%s] not implemented.', __METHOD__
        ));
    }

    /**
     * @param array $parameters
     * @return mixed
     * @throws \Tmconsulting\Uniteller\Exception\NotImplementedException
     */
    public function card($parameters)
    {
        throw new NotImplementedException(sprintf(
            'In current moment, feature [%s] not implemented.', __METHOD__
        ));
    }

    /**
     * Подгружаем собственный HttpManager с газлом в качестве клиента, если
     * не был задан свой, перед выполнением запроса.
     *
     * @param $name
     * @param $parameters
     * @return Order|mixed
     */
    private function callRequestFor($name, $parameters)
    {
        /** @var RequestInterface $request */
        $request = $this->{'get' . ucfirst($name) . 'Request'}();

        return $request->execute(
            $this->getHttpManager(),
            $this->getParameters($parameters)
        );
    }

    /**
     * Verify signature when Client will be send callback request.
     *
     * @param array $params
     * @return bool
     */
    public function verifyCallbackRequest(array $params)
    {
        return $this->signatureCallback
            ->setOrderId(array_get($params, 'Order_ID'))
            ->setStatus(array_get($params, 'Status'))
            ->setFields(array_except($params, ['Order_ID', 'Status', 'Signature']))
            ->setPassword($this->getPassword())
            ->verify(array_get($params, 'Signature'));
    }
}
