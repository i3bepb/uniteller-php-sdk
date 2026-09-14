<?php

namespace Tmconsulting\Uniteller\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Decoded body with HTTP context for endpoint-specific exceptions.
 */
class DecodedResponse
{
    /** @var array */
    private $data;
    /** @var RequestInterface */
    private $request;
    /** @var ResponseInterface */
    private $response;

    public function __construct(array $data, RequestInterface $request, ResponseInterface $response)
    {
        $this->data = $data;
        $this->request = $request;
        $this->response = $response;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
