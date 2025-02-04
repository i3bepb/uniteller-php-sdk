<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Parameter\Traits\AmountValidation;

class AmountParameter extends BaseParameter
{
    use AmountValidation;

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function validate($value): void
    {
        $this->assertValidAmount($this->unitellerName, $value);
    }
}
