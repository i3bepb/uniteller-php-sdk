<?php

namespace Tmconsulting\Uniteller\Support;

use Tmconsulting\Uniteller\Exception\AuthConfirmIsNotAllowedException;
use Tmconsulting\Uniteller\Exception\AuthenticationException;
use Tmconsulting\Uniteller\Exception\InvalidResponseException;
use Tmconsulting\Uniteller\Exception\OperationFailedException;

/**
 * Подготавливает диагностические данные исключения и безопасное сообщение лога.
 */
final class ExceptionContext
{
    /**
     * Проверенные фиксированные сообщения для конкретных классов исключений SDK.
     *
     * Класс и сообщение должны совпасть полностью. Произвольный текст ответа
     * провайдера и сообщения пользовательских подклассов не считаются безопасными.
     *
     * @var array<string, array<string, bool>>
     */
    private const SAFE_MESSAGES = [
        AuthConfirmIsNotAllowedException::class => ['Authorization confirm is not allowed' => true],
        AuthenticationException::class => ['Authentication error' => true],
        OperationFailedException::class => ['The operation failed' => true],
        InvalidResponseException::class => [
            'Invalid JSON response' => true,
            'Empty XML response' => true,
            'Invalid XML response' => true,
            'Cannot decode XML response' => true,
            'Missing Result in FiscalConfirm response' => true,
            'Invalid Result in FiscalConfirm response' => true,
            'Invalid ErrorMessage in FiscalConfirm response' => true,
            'Invalid Receipt in FiscalConfirm response' => true,
        ],
    ];

    /**
     * Возвращает класс, код и место возникновения исключения или ошибки PHP.
     *
     * Вызывается в месте логирования, чтобы передать логгеру готовый массив
     * вместо исходного объекта Throwable.
     *
     * @param \Throwable $exception Исключение или ошибка, для которой формируется контекст.
     *
     * @return array{class: string, code: int|string, file: string, line: int} Диагностические данные.
     */
    public static function fromThrowable(\Throwable $exception): array
    {
        return [
            'class' => get_class($exception),
            'code'  => $exception->getCode(),
            'file'  => $exception->getFile(),
            'line'  => $exception->getLine(),
        ];
    }

    /**
     * Дополняет сообщение лога только проверенным фиксированным сообщением исключения SDK.
     *
     * Для неизвестного класса или текста возвращает исходное сообщение без изменений.
     * Проверка точного класса не распространяет доверие на пользовательские подклассы.
     *
     * @param \Throwable $exception Исключение или ошибка, возникшая при выполнении операции.
     * @param string $fallbackMessage Постоянное сообщение лога для общего случая.
     *
     * @return string Сообщение лога с безопасным описанием ошибки, если оно распознано.
     */
    public static function logMessage(\Throwable $exception, string $fallbackMessage): string
    {
        $class = get_class($exception);
        if (!isset(self::SAFE_MESSAGES[$class])) {
            return $fallbackMessage;
        }
        $message = $exception->getMessage();
        if (!isset(self::SAFE_MESSAGES[$class][$message])) {
            return $fallbackMessage;
        }

        return $fallbackMessage . ': ' . $message;
    }
}
