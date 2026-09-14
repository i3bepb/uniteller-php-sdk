<?php

namespace Tmconsulting\Uniteller\Confirm;

use Tmconsulting\Uniteller\Exception\InvalidResponseException;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;

class FiscalConfirmResultParser
{
    /** @var ParserReceiptFromBase64 */
    private $parserReceipt;

    public function __construct(ParserReceiptFromBase64 $parserReceipt)
    {
        $this->parserReceipt = $parserReceipt;
    }

    public function parse(array $data): FiscalConfirmResult
    {
        if (!array_key_exists('Result', $data)) {
            throw new InvalidResponseException('Missing Result in FiscalConfirm response');
        }
        $result = $data['Result'];
        if ((!is_int($result) && !is_string($result))
            || !preg_match('/^[0-9]+$/D', (string)$result)
            || filter_var($result, FILTER_VALIDATE_INT) === false
        ) {
            throw new InvalidResponseException('Invalid Result in FiscalConfirm response');
        }
        $errorMessage = null;
        if (array_key_exists('ErrorMessage', $data)) {
            // An empty XML element is decoded as an empty array.
            if ($data['ErrorMessage'] === []) {
                $errorMessage = '';
            } elseif (is_string($data['ErrorMessage'])) {
                $errorMessage = $data['ErrorMessage'];
            } else {
                throw new InvalidResponseException('Invalid ErrorMessage in FiscalConfirm response');
            }
        }
        $receipts = [];
        if (array_key_exists('Receipt', $data)) {
            if (!is_string($data['Receipt'])) {
                throw new InvalidResponseException('Invalid Receipt in FiscalConfirm response');
            }
            $receipts = $this->parserReceipt->parse($data['Receipt']);
        }

        return new FiscalConfirmResult((int)$result, $errorMessage, $receipts);
    }
}
