<?php

namespace Tmconsulting\Uniteller\Dependency;

interface ContainerAwareInterface
{
    /**
     * @param \Tmconsulting\Uniteller\Dependency\Container $container
     */
    public function setContainer(Container $container);
}
