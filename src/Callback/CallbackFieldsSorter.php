<?php

namespace Tmconsulting\Uniteller\Callback;

use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;

/**
 * Общий порядок CallbackFields для исходящего запроса и проверки уведомления.
 * Здесь учитывается порядок полей как уведомлений без чеков, так и с чеками.
 */
final class CallbackFieldsSorter
{
    /**
     * @param string[] $fields Имена дополнительных полей.
     *
     * @return string[]
     */
    public static function sort(array $fields): array
    {
        $priority = [CallbackFields::APPROVAL_CODE, CallbackFields::BILL_NUMBER, CallbackFields::TOTAL];
        $remaining = array_values(array_diff(array_unique($fields), $priority));
        sort($remaining, SORT_STRING);

        return array_merge(array_values(array_intersect($priority, $fields)), $remaining);
    }
}
