<?php

namespace Tmconsulting\Uniteller\Builder;

interface BuilderInterface
{
    /**
     * Создает объект Builder и наполняет из входящего массива параметров
     *
     * @param array $parameters Массив с параметрами
     *
     * @return \Tmconsulting\Uniteller\Builder\BuilderInterface
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public static function setFromArray(array $parameters): BuilderInterface;
}
