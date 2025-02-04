<?php

namespace Tmconsulting\Uniteller\Dependency;

trait ContainerAwareTrait
{
    /**
     * @var \Tmconsulting\Uniteller\Dependency\Container
     */
    protected $container;

    /**
     * @param \Tmconsulting\Uniteller\Dependency\Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
