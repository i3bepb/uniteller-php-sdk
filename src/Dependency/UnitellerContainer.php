<?php

namespace Tmconsulting\Uniteller\Dependency;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Client;
use Tmconsulting\Uniteller\ClientInterface;
use Tmconsulting\Uniteller\Payment\Payment;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Results\ResultsRequest;
use Tmconsulting\Uniteller\Signature\SignaturePayment;

/**
 * Dependency injection container for Uniteller classes
 */
class UnitellerContainer implements ContainerInterface
{
    /**
     * @var array
     */
    private $classes = [
        LoggerInterface::class  => null,
        ClientInterface::class  => Client::class,
        SignaturePayment::class => SignaturePayment::class,
        PaymentBuilder::class   => PaymentBuilder::class,
        Payment::class          => Payment::class,
        ResultsBuilder::class   => ResultsBuilder::class,
        ResultsRequest::class   => ResultsRequest::class,
    ];

    /**
     * @var array
     */
    private $resolved = [];

    /**
     * @param array $classes
     * @param array $resolved Ready entity of an object.
     */
    public function __construct(array $classes = [], array $resolved = [])
    {
        $this->classes = array_merge($this->classes, $classes);
        $this->resolved = array_merge([ContainerInterface::class => $this], $resolved);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T Entry
     *
     * @throws \Psr\Container\NotFoundExceptionInterface No entry was found for this identifier.
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException();
        }
        if (isset($this->resolved[$id])) {
            return $this->resolved[$id];
        }

        $class = $this->classes[$id];
        $entry = new $class();
        if ($entry instanceof ContainerAwareInterface) {
            $entry->setContainer($this->get(ContainerInterface::class));
        }
        if ($entry instanceof LoggerAwareInterface) {
            $entry->setLogger($this->get(LoggerInterface::class));
        }

        $this->resolved[$id] = $entry;
        return $entry;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->classes) || isset($this->resolved[$id]);
    }
}
