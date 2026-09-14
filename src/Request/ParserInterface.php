<?php

namespace Tmconsulting\Uniteller\Request;

interface ParserInterface
{
    public function parse(string $response): array;
}
