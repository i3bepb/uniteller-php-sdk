<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Callback\CallbackFieldsSorter;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;

class CallbackFieldsParameter extends BaseParameter
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
        $this->value = join(' ', CallbackFieldsSorter::sort($value));
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
        $allowed = CallbackFields::toArray();
        foreach ($value as $field) {
            if (!is_string($field) || !in_array($field, $allowed, true)) {
                throw new NotValidParameterException(
                    "Invalid {$this->unitellerName}: each value must be one of " . implode(', ', $allowed) . '.'
                );
            }
        }
    }
}
