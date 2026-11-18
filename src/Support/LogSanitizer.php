<?php

namespace Tmconsulting\Uniteller\Support;

use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;

/**
 * Подготавливает данные к записи в лог, маскируя чувствительные значения.
 */
final class LogSanitizer
{
    /**
     * Маркер, заменяющий скрытое значение целиком.
     *
     * @var string
     */
    private const MASK = '[REDACTED]';

    /**
     * Документированные поля Uniteller, скрываемые при маскировании персональных данных.
     *
     * @var array<string, string>
     */
    private const PERSONAL_FIELDS = [
        UnitellerParameterName::EMAIL             => UnitellerParameterName::EMAIL,
        UnitellerParameterName::PHONE             => UnitellerParameterName::PHONE,
        UnitellerParameterName::PHONE_VERIFIED    => UnitellerParameterName::PHONE_VERIFIED,
        UnitellerParameterName::DEST_PHONE_NUM    => UnitellerParameterName::DEST_PHONE_NUM,
        UnitellerParameterName::FIRST_NAME        => UnitellerParameterName::FIRST_NAME,
        UnitellerParameterName::LAST_NAME         => UnitellerParameterName::LAST_NAME,
        UnitellerParameterName::MIDDLE_NAME       => UnitellerParameterName::MIDDLE_NAME,
        UnitellerParameterName::ADDRESS           => UnitellerParameterName::ADDRESS,
        UnitellerParameterName::COUNTRY           => UnitellerParameterName::COUNTRY,
        UnitellerParameterName::STATE             => UnitellerParameterName::STATE,
        UnitellerParameterName::CITY              => UnitellerParameterName::CITY,
        UnitellerParameterName::ZIP               => UnitellerParameterName::ZIP,
        UnitellerParameterName::CUSTOMER_IDP      => UnitellerParameterName::CUSTOMER_IDP,
        UnitellerParameterName::I_DATA            => UnitellerParameterName::I_DATA,
        UnitellerParameterName::SIGNATURE         => UnitellerParameterName::SIGNATURE,
        UnitellerParameterName::RECEIPT_SIGNATURE => UnitellerParameterName::RECEIPT_SIGNATURE,
        SFields::CARD_HOLDER                      => SFields::CARD_HOLDER,
        SFields::IP_ADDRESS                       => SFields::IP_ADDRESS,
    ];

    /**
     * Возвращает копию параметров с замаскированными чувствительными данными.
     *
     * Рекурсивно обрабатывает массивы и распознаёт специальные поля headers, body, raw_post, uri, url и query.
     * Формат произвольных строковых значений по их содержимому не определяется.
     *
     * Пароли заменяются целиком независимо от политики. Персональные данные и чеки скрываются только
     * при включённой настройке маскирования. Чеки обрабатываются как непрозрачные значения:
     * заменяются целиком на MASK либо возвращаются без изменений, без разбора их содержимого.
     *
     * @param array $parameters Параметры или структурированный контекст записи.
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования данных.
     * @param int $depth Текущая глубина обработки; при достижении 64 вложенные массивы скрываются целиком.
     *
     * @return array Обработанная копия параметров с сохранением ключей.
     */
    public static function sanitize(array $parameters, LoggingPolicy $policy, int $depth = 0): array
    {
        $contentType = self::contentType($parameters['headers'] ?? []);
        foreach ($parameters as $key => $value) {
            $name = (string)$key;
            if (self::isSecret($name)
                || ($policy->shouldMaskPersonalData() && (self::isReceipt($name) || self::isPersonalData($name)))
            ) {
                $parameters[$key] = self::MASK;
            } elseif (self::isReceipt($name)) {
                continue;
            } elseif (is_array($value)) {
                // Поле receipt в контексте SDK; регистр полей самого API не меняется.
                if ($name === 'uniteller' && $policy->shouldMaskPersonalData() && array_key_exists('receipt', $value)) {
                    $value['receipt'] = self::MASK;
                }
                $parameters[$key] = $depth < 64 ? self::sanitize($value, $policy, $depth + 1) : self::MASK;
            } elseif (is_string($value)) {
                if ($value === self::MASK) {
                    continue;
                }
                if (in_array($name, ['body', 'raw_post'], true)) {
                    $parameters[$key] = self::sanitizeBody($value, $policy, $contentType, $depth + 1);
                } elseif (in_array($name, [
                    'uri',
                    'url',
                    UnitellerParameterName::URL_RETURN,
                    UnitellerParameterName::URL_RETURN_OK,
                    UnitellerParameterName::URL_RETURN_NO,
                    UnitellerParameterName::BACK_URL,
                    UnitellerParameterName::DEEP_LINK,
                    SFields::LK_ORDER_URL,
                ], true)) {
                    $parameters[$key] = self::sanitizeUri($value, $policy);
                } elseif (in_array($name, ['query', 'querystring'], true)) {
                    $parameters[$key] = self::sanitizeBody($value, $policy, 'application/x-www-form-urlencoded', $depth + 1);
                }
            } elseif (is_object($value)) {
                // Never hand arbitrary objects (and their internal HTTP data) to a log formatter.
                $parameters[$key] = '[object ' . get_class($value) . ']';
            }
        }

        return $parameters;
    }

    /**
     * Подготавливает к логированию параметры вычисления подписи.
     *
     * Маскирует значения по документированным именам полей Uniteller.
     * Те же ключи используются для исходных параметров и промежуточных хешей,
     * поэтому пароль и персональные данные скрываются в обоих представлениях.
     *
     * @param array $parameters Параметры вычисления подписи в порядке их передачи.
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования данных.
     *
     * @return array Обработанная копия параметров подписи.
     */
    public static function sanitizeSignatureParameters(array $parameters, LoggingPolicy $policy): array
    {
        return self::sanitize($parameters, $policy);
    }

    /**
     * Подготавливает URI к логированию, маскируя параметры строки запроса.
     *
     * Сохраняет схему, хост, порт и путь; имя пользователя, пароль и фрагмент
     * в результат не включаются. Query-параметры обрабатываются как
     * form-urlencoded. Содержимое пути не маскируется.
     *
     * @param string $uri Исходный URI.
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования query-параметров.
     *
     * @return string Обработанный URI или маркер [invalid URI], если parse_url() завершился ошибкой.
     */
    public static function sanitizeUri(string $uri, LoggingPolicy $policy): string
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            return '[invalid URI]';
        }
        $result = isset($parts['scheme']) ? $parts['scheme'] . ':' : '';
        if (isset($parts['host'])) {
            $result .= '//' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        }
        $result .= $parts['path'] ?? '';
        if (isset($parts['query'])) {
            $result .= '?' . self::sanitizeBody($parts['query'], $policy, 'application/x-www-form-urlencoded');
        }

        return $result;
    }

    /**
     * Подготавливает строковое тело сообщения к логированию.
     *
     * Определяет JSON, XML и CSV по Content-Type или содержимому; form-urlencoded обрабатывается при явно указанном типе.
     * После разбора маскирует поля и заново сериализует данные, поэтому исходное форматирование может измениться.
     * XML загружается без сетевого доступа, документы с DTD отклоняются.
     *
     * Некорректное или неподдерживаемое тело заменяется маркером его размера.
     * При глубине больше 64 возвращается маркер маскирования целого значения.
     * Пустая строка и строка только из пробельных символов возвращаются без изменений в пределах допустимой глубины.
     *
     * @param string $body Исходное тело сообщения.
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования данных.
     * @param string $contentType Тип содержимого в нижнем регистре; пустая строка включает определение по содержимому.
     * @param int $depth Текущая глубина обработки вложенных данных.
     *
     * @return string Обработанное тело или маркер, заменяющий его содержимое.
     */
    public static function sanitizeBody(string $body, LoggingPolicy $policy, string $contentType = '', int $depth = 0): string
    {
        if ($body === '') {
            return '';
        }
        if ($depth > 64) {
            return self::MASK;
        }
        $trimmed = ltrim($body);
        if ($trimmed === '') {
            return $body;
        }
        if (strpos($contentType, 'json') !== false || $trimmed[0] === '{' || $trimmed[0] === '[') {
            $data = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $encoded = json_encode(
                    self::sanitize($data, $policy, $depth),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                return $encoded === false ? self::omittedBody($body) : $encoded;
            }
            return self::omittedBody($body);
        }
        if (strpos($contentType, 'xml') !== false || $trimmed[0] === '<') {
            $previous = libxml_use_internal_errors(true);
            try {
                $document = new \DOMDocument();
                if (!$document->loadXML($body, LIBXML_NONET) || $document->doctype !== null) {
                    return self::omittedBody($body);
                }
                self::sanitizeXmlElement($document->documentElement, $policy);
                foreach ($document->childNodes as $child) {
                    if ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                        $child->nodeValue = self::MASK;
                    }
                }
                return $document->saveXML() ?: self::omittedBody($body);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
        }
        if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            // parse_str has a configurable input-variable limit. Never log a partially parsed body.
            $limit = (int)ini_get('max_input_vars');
            if ($limit > 0 && substr_count($body, '&') + substr_count($body, ';') + 1 > $limit) {
                return self::omittedBody($body);
            }
            $invalid = false;
            set_error_handler(static function () use (&$invalid) {
                $invalid = true;
                return true;
            });
            try {
                parse_str($body, $data);
            } finally {
                restore_error_handler();
            }
            if ($invalid) {
                return self::omittedBody($body);
            }
            return http_build_query(self::sanitize($data, $policy, $depth));
        }
        if (strpos($contentType, 'csv') !== false || strpos($body, "\n") !== false) {
            return self::sanitizeCsv($body, $policy, $depth);
        }

        return self::omittedBody($body);
    }

    /**
     * Рекурсивно маскирует содержимое XML-элемента и его атрибутов на месте.
     *
     * Имя поля определяется по локальному имени элемента, а для var, field
     * и param — по атрибуту name. При включённом маскировании персональных данных
     * чеки скрываются целиком с удалением атрибутов, кроме имени поля у обёртки.
     * При выключенном маскировании элементы чеков сохраняются без обработки содержимого.
     *
     * Чувствительные значения и элементы глубже 64 заменяются маркером.
     * Вложенные JSON-строки обрабатываются через sanitizeBody(); содержимое
     * комментариев и инструкций обработки скрывается.
     *
     * @param \DOMElement $element Элемент DOM, который изменяется при обработке.
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования персональных данных.
     * @param int $depth Текущая глубина обхода дерева XML.
     *
     * @return void
     */
    private static function sanitizeXmlElement(\DOMElement $element, LoggingPolicy $policy, int $depth = 0): void
    {
        $name = $element->localName;
        $fieldName = in_array($name, ['var', 'field', 'param'], true)
            ? $element->getAttribute('name') : $name;
        if (self::isReceipt($fieldName)) {
            if (!$policy->shouldMaskPersonalData()) {
                return;
            }
            $fieldAttribute = $element->getAttribute('name');
            while ($element->attributes->length > 0) {
                $element->removeAttributeNode($element->attributes->item(0));
            }
            if ($fieldName !== $name) {
                $element->setAttribute('name', $fieldAttribute);
            }
            $element->textContent = self::MASK;
            return;
        }
        $masked = $depth > 64 || self::isSecret($fieldName)
            || ($policy->shouldMaskPersonalData() && self::isPersonalData($fieldName));
        foreach ($element->attributes as $attribute) {
            $attributeName = $attribute->localName;
            if ($masked || self::isSecret($attributeName)
                || ($policy->shouldMaskPersonalData()
                    && (self::isReceipt($attributeName) || self::isPersonalData($attributeName)))) {
                $attribute->value = self::MASK;
            }
        }
        if ($masked) {
            $element->textContent = self::MASK;
            return;
        }
        if ($element->childNodes->length === 1 && preg_match('/^\s*[\[{]/', $element->textContent)) {
            $element->textContent = self::sanitizeBody($element->textContent, $policy, '', $depth + 1);
            return;
        }
        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                self::sanitizeXmlElement($child, $policy, $depth + 1);
            } elseif ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                $child->nodeValue = self::MASK;
            }
        }
    }

    /**
     * Маскирует значения CSV по именам колонок из первой строки.
     *
     * Выбирает разделитель из точки с запятой, запятой и табуляции по количеству
     * колонок в первой строке. Требует минимум две колонки с уникальными именами,
     * начинающимися с латинской буквы или подчёркивания и содержащими только
     * латинские буквы, цифры, подчёркивания, пробелы и дефисы.
     *
     * Каждая строка обрабатывается через sanitize() как ассоциативный массив.
     * При некорректном заголовке или несовпадении числа значений и колонок
     * всё тело заменяется маркером его размера.
     *
     * @param string $body Исходный CSV с заголовком колонок.
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования данных.
     * @param int $depth Глубина обработки, передаваемая в sanitize().
     *
     * @return string Заново сформированный CSV или маркер неразобранного тела.
     */
    private static function sanitizeCsv(string $body, LoggingPolicy $policy, int $depth): string
    {
        $firstLine = strtok($body, "\r\n");
        $delimiter = ';';
        foreach ([',', "\t"] as $candidate) {
            if (count(str_getcsv($firstLine, $candidate)) > count(str_getcsv($firstLine, $delimiter))) {
                $delimiter = $candidate;
            }
        }
        $input = fopen('php://temp', 'r+');
        $output = fopen('php://temp', 'r+');
        try {
            fwrite($input, $body);
            rewind($input);
            $header = fgetcsv($input, 0, $delimiter);
            if (!$header || count($header) < 2 || count(array_unique($header)) !== count($header)) {
                return self::omittedBody($body);
            }
            foreach ($header as $name) {
                if (!preg_match('/^[A-Za-z_][A-Za-z0-9_ -]*$/D', $name)) {
                    return self::omittedBody($body);
                }
            }
            fputcsv($output, $header, $delimiter);
            while (($row = fgetcsv($input, 0, $delimiter)) !== false) {
                if (count($row) !== count($header)) {
                    return self::omittedBody($body);
                }
                $record = self::sanitize(array_combine($header, $row), $policy, $depth);
                fputcsv($output, array_values($record), $delimiter);
            }
            rewind($output);
            return stream_get_contents($output);
        } finally {
            fclose($input);
            fclose($output);
        }
    }

    /**
     * Извлекает тип содержимого из массива HTTP-заголовков.
     *
     * Имя Content-Type сопоставляется без учёта регистра. Массив значений
     * объединяется через точку с запятой, результат приводится к нижнему регистру.
     *
     * @param mixed $headers Массив заголовков; остальные типы не обрабатываются.
     *
     * @return string Значение Content-Type или пустая строка, если оно не найдено.
     */
    private static function contentType($headers): string
    {
        if (is_array($headers)) {
            foreach ($headers as $name => $values) {
                if (strtolower($name) === 'content-type') {
                    return strtolower(is_array($values) ? implode(';', $values) : (string)$values);
                }
            }
        }
        return '';
    }

    /**
     * Проверяет, обозначает ли поле один из документированных паролей Uniteller.
     * Распознаёт Password и bookingcom_pincode. Эти поля скрываются независимо от политики персональных данных.
     *
     * @param string $name Имя поля.
     *
     * @return bool true, если значение поля нужно скрывать всегда.
     */
    private static function isSecret(string $name): bool
    {
        return $name === UnitellerParameterName::PASSWORD || $name === SFields::BOOKINGCOM_PINCODE;
    }

    /**
     * Проверяет имя поля по списку документированных персональных данных Uniteller.
     *
     * Выполняет поиск по ключу PERSONAL_FIELDS без перебора массива.
     * Имя сопоставляется точно, включая регистр и разделители.
     *
     * @param string $name Имя поля.
     *
     * @return bool true, если поле входит в список персональных данных.
     */
    private static function isPersonalData(string $name): bool
    {
        return isset(self::PERSONAL_FIELDS[$name]);
    }

    /**
     * Проверяет, содержит ли поле чек или набор чеков.
     *
     * Распознаёт имена Receipt и Receipts. При включённом маскировании персональных данных значение скрывается
     * целиком иначе сохраняется без разбора содержимого.
     *
     * @param string $name Имя поля.
     *
     * @return bool true, если поле обозначает чек или набор чеков.
     */
    private static function isReceipt(string $name): bool
    {
        return $name === UnitellerParameterName::RECEIPT || $name === 'Receipts';
    }

    /**
     * Формирует маркер тела, содержимое которого не удалось обработать.
     *
     * Включает только размер исходной строки в байтах, без её содержимого.
     *
     * @param string $body Исходное тело сообщения.
     *
     * @return string Маркер вида [unparsed body: N bytes].
     */
    private static function omittedBody(string $body): string
    {
        return '[unparsed body: ' . strlen($body) . ' bytes]';
    }
}
