<?php

namespace Tmconsulting\Uniteller\Dependency;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container);
}
