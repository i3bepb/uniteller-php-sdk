<?php

namespace Tmconsulting\Uniteller\Common;

use Tmconsulting\Uniteller\ArraybleInterface;

trait GetParametersFromBuilder
{
    /**
     * @param array|\Tmconsulting\Uniteller\ArraybleInterface $parameters
     *
     * @return array
     */
    private function getParameters($parameters): array
    {
        if ($parameters instanceof ArraybleInterface) {
            return $parameters->toArray();
        }

        return $parameters;
    }
}
