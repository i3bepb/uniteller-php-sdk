<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\ParameterNotRegisteredException;
use Tmconsulting\Uniteller\Exception\Parameter\UnknownParameterNameException;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;

class ParameterBag
{
    /**
     * @var \Tmconsulting\Uniteller\Parameter\BaseParameter[]
     */
    protected $parameters = [];

    /**
     * @param \Tmconsulting\Uniteller\Parameter\ParameterInterface $parameter
     *
     * @return void
     */
    public function add(ParameterInterface $parameter)
    {
        $this->parameters[$parameter->getCanonicalName()] = $parameter;
    }

    /**
     * @param string $name Каноническое имя параметра внутри этой библиотеки.
     *
     * @return \Tmconsulting\Uniteller\Parameter\ParameterInterface
     */
    public function get(string $name)
    {
        if (!in_array($name, CanonicalParameterName::toArray(), true)) {
            throw new UnknownParameterNameException("Parameter \"$name\" is not a exist parameter.");
        }
        if (!isset($this->parameters[$name])) {
            throw new ParameterNotRegisteredException("Parameter \"$name\" is not registered in current context.");
        }

        return $this->parameters[$name];
    }

    /**
     * @param string $name Каноническое имя параметра внутри этой библиотеки.
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * @return \Tmconsulting\Uniteller\Parameter\ParameterInterface[]
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function handleDynamicCall(string $method, array $arguments = [])
    {
        if (0 === strpos($method, 'set')) {
            $canonicalName = substr($method, 3);
            $value = array_key_exists(0, $arguments) ? $arguments[0] : null;
            $parameter = $this->get($canonicalName);
            $parameter->setValue($value);

            return $parameter;
        }
        if (0 === strpos($method, 'get')) {
            $canonicalName = substr($method, 3);

            return $this->get($canonicalName)->getValue();
        }
        if (0 === strpos($method, 'has')) {
            $canonicalName = substr($method, 3);

            return $this->get($canonicalName)->hasValue();
        }
        throw new \BadMethodCallException("Method \"$method\" is not supported by ParameterBag.");
    }
}
