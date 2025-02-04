<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;

class SFieldsParameter extends BaseParameter
{
    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        if ($value === null) {
            $this->value = null;
            return;
        }
        $this->validate($value);
        sort($value);
        $this->value = join(';', $value);
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function validate($value): void
    {
        if (!is_array($value)) {
            throw new NotValidParameterException("Invalid {$this->unitellerName}: must be array.");
        }
        $fields = SFields::toArray();
        foreach ($value as $field) {
            if (!is_string($field) || !in_array($field, $fields, true)) {
                throw new NotValidParameterException(
                    "Invalid {$this->unitellerName}: each value must be one of " . implode(', ', $fields) . '.'
                );
            }
        }
    }
}
