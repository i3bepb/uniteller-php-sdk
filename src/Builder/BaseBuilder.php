<?php

namespace Tmconsulting\Uniteller\Builder;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Tmconsulting\Uniteller\Dependency\DebugAwareInterface;
use Tmconsulting\Uniteller\Dependency\DebugAwareTrait;
use Tmconsulting\Uniteller\Exception\Configuration\ConfigurationException;
use Tmconsulting\Uniteller\Parameter\ParameterBag;
use Tmconsulting\Uniteller\Parameter\ParameterInterface;
use Tmconsulting\Uniteller\Parameter\Traits\TrimmedStringValidation;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Signature\Signature;

/**
 * Основа всех сценариев (кейсов) в API Uniteller.
 */
abstract class BaseBuilder implements BuilderInterface, LoggerAwareInterface, DebugAwareInterface
{
    use LoggerAwareTrait;
    use DebugAwareTrait;
    use TrimmedStringValidation;

    /**
     * @var \Tmconsulting\Uniteller\Signature\Signature
     */
    protected $signatureCreator;

    /**
     * @var \Tmconsulting\Uniteller\Parameter\ParameterBag
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $endpoint = ApiEndpoints::PAYMENT;

    /**
     * @param \Tmconsulting\Uniteller\Signature\Signature $signatureCreator
     */
    public function __construct(Signature $signatureCreator)
    {
        $this->signatureCreator = $signatureCreator;
        $this->parameters = new ParameterBag();
        $this->registerParameters();
    }

    /**
     * @param string $endpoint
     *
     * @return static
     *
     * @throws \Tmconsulting\Uniteller\Exception\Configuration\ConfigurationException
     */
    public function setEndpoint(string $endpoint)
    {
        $allowed = ApiEndpoints::toArray();
        if (!in_array($endpoint, $allowed, true)) {
            throw new ConfigurationException(
                "Invalid Endpoint. Allowed values: " . implode(', ', $allowed) . '.'
            );
        }
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return void
     */
    abstract protected function registerParameters(): void;

    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $result = $this->parameters->handleDynamicCall($method, $arguments);

        return $result instanceof ParameterInterface ? $this : $result;
    }

    /**
     * @return string|null Формат ответа.
     */
    public function getResponseFormat(): ?string
    {
        return null;
    }

    /**
     * Проверка присутствия обязательных полей.
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    abstract protected function validateRequired(): void;

    /**
     * Возвращает массив со значениями параметров.
     *
     * @return array
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function toArray(): array
    {
        $this->validateRequired();

        $arr = [];
        foreach ($this->parameters->all() as $parameter) {
            if ($parameter->isShouldBeSent() && $parameter->hasValue()) {
                $arr[$parameter->getUnitellerName()] = $parameter->getValue();
            }
        }

        return $arr;
    }
}
