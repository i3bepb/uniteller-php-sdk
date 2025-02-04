<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

class BooleanParameter extends BaseParameter
{
    /**
     * @param mixed $value
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function validate($value): void
    {
        if (!is_bool($value)) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must be boolean."
            );
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value ? '1' : null;
    }
}
