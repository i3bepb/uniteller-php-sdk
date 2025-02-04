<?php

namespace Tmconsulting\Uniteller\Exception;

use Psr\Http\Client\NetworkExceptionInterface;

class ServerErrorException extends UnitellerException implements NetworkExceptionInterface
{

}