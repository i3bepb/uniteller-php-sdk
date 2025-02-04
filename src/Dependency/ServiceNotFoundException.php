<?php

namespace Tmconsulting\Uniteller\Dependency;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    /**
     * @var string
     */
    protected $serviceId;

    /**
     * @param string $serviceId
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $serviceId, $code = 0, \Throwable $previous = null)
    {
        $this->serviceId = $serviceId;

        parent::__construct(
            sprintf('Service "%s" was not found in container.', $serviceId),
            $code,
            $previous
        );
    }

    /**
     * @return string
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }
}
