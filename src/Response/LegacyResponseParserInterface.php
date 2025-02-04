<?php

namespace Tmconsulting\Uniteller\Response;

use Tmconsulting\Uniteller\Request\DecodedResponse;

/**
 * Разбирает заказы и ошибки в ответах методов results, confirm и recurrent.
 */
interface LegacyResponseParserInterface
{
    /**
     * @param DecodedResponse $response Декодированный ответ с исходными HTTP-сообщениями.
     *
     * @return \Tmconsulting\Uniteller\Order\Order[] Заказы с декодированными чеками.
     *
     * @throws \Tmconsulting\Uniteller\Exception\ErrorException Если ответ содержит ошибку Uniteller.
     * @throws \RuntimeException Если не удалось декодировать чеки.
     */
    public function parse(DecodedResponse $response): array;
}
