<?php

namespace Tmconsulting\Uniteller\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tmconsulting\Uniteller\Exception\ExceptionFactory;

class ParserJson implements ParserInterface
{
    /**
     * @var \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64
     */
    private $parserReceipt;

    /**
     * @param \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64 $parserReceipt
     */
    public function __construct(ParserReceiptFromBase64 $parserReceipt)
    {
        $this->parserReceipt = $parserReceipt;
    }

    public function parse(string $response): array
    {
        $arr = json_decode($response, true);
        if (!empty($arr['Receipt'])) {
            $arr['Receipt'] = $this->parserReceipt->parse($arr['Receipt']);
        }
        return $arr;
    }

    public function parseOrders(array $data): array
    {
        return $data;
    }

    /**
     * @param $data
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \Tmconsulting\Uniteller\Exception\ErrorException
     */
    public function parseErrors($data, RequestInterface $request, ResponseInterface $response)
    {
        if (!empty($data['Code']) && $data['Code'] !== '00') {
            throw ExceptionFactory::create($data['Code'], $data['Note'], $request, $response);
        }
    }
}