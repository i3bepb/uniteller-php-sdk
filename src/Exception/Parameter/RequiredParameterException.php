<?php

namespace Tmconsulting\Uniteller\Exception\Parameter;

class RequiredParameterException extends \Exception
{
    public function __construct(string $parameter)
    {
        parent::__construct("Parameter {$parameter} empty or not set");
    }
}
