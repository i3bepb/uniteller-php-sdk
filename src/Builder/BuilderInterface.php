<?php

namespace Tmconsulting\Uniteller\Builder;

interface BuilderInterface
{
    /**
     * Возвращает массив со значениями параметров.
     *
     * @return array
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function toArray(): array;

    /**
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * @return string|null Формат ответа.
     */
    public function getResponseFormat(): ?string;

    /**
     * Выполнение.
     *
     * @return mixed
     */
    public function process();
}
