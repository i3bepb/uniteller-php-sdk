<?php

namespace Tmconsulting\Uniteller\Request;

use Tmconsulting\Uniteller\Exception\InvalidResponseException;

class ParserJson implements ParserInterface
{
    public function parse(string $response): array
    {
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new InvalidResponseException('Invalid JSON response');
        }

        return $data;
    }
}
