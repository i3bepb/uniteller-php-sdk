<?php

namespace Tmconsulting\Uniteller\Dependency;

trait DebugAwareTrait
{
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }
}
