<?php

namespace Tmconsulting\Uniteller\Dependency;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends \Exception implements NotFoundExceptionInterface
{

}
