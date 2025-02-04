<?php

namespace Tmconsulting\Uniteller\Dependency;

interface DebugAwareInterface
{
    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug);
}
