<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\Common\EnumToArray;

final class Format
{
    use EnumToArray;

    /**
     * Поля разделены разделителем, указанным в поле Delimiter
     */
    const CSV = 1;
    /**
     * Web Distributed Data eXchange
     */
    const WDDX = 2;
    /**
     * Каждое поле заключено в разделители, указанные в OpenDelimiter и CloseDelimiter
     */
    const IN_BRACKETS = 3;
    /**
     * Каждое поле заключено в разделители, указанные в OpenDelimiter и CloseDelimiter
     */
    const XML = 4;
    /**
     * Каждое поле заключено в разделители, указанные в OpenDelimiter и CloseDelimiter
     */
    const SOAP = 5;
}
