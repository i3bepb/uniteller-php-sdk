<?php

namespace Tmconsulting\Uniteller\Dependency;

class InvalidServiceDefinitionException extends \InvalidArgumentException
{
    /**
     * @param string $serviceId
     * @param string $message
     */
    public function __construct(string $serviceId, string $message)
    {
        parent::__construct(
            sprintf('Ошибка определения сервиса "%s": %s', $serviceId, $message)
        );
    }
}