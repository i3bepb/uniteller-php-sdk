<?php

namespace Tmconsulting\Uniteller\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ParserInterface
{
    /**
     * @param \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64 $parserReceipt
     */
    public function __construct(ParserReceiptFromBase64 $parserReceipt);

    /**
     * @param string $response
     *
     * @return array
     */
    public function parse(string $response): array;

    /**
     * @param array $data
     *
     * @return \Tmconsulting\Uniteller\Order\Order[]
     *
     * @throws \Exception
     */
    public function parseOrders(array $data): array;

    /**
     * @param $data
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \Tmconsulting\Uniteller\Exception\ErrorException
     */
    public function parseErrors($data, RequestInterface $request, ResponseInterface $response);
}
