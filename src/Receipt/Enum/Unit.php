<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Мера количества предмета расчета.
 */
class Unit
{
    use EnumToArrayTrait;

    /**
     * Шт./ед.
     * Применяется для предметов расчета, которые могут быть реализованы поштучно или единицами.
     */
    const PIECE = 0;
    /**
     * Грамм (г)
     */
    const GRAM = 10;
    /**
     * Килограмм (кг)
     */
    const KILOGRAM = 11;
    /**
     * Тонна (т)
     */
    const TONNE = 12;
    /**
     * Сантиметр (см)
     */
    const CENTIMETER = 20;
    /**
     * Дециметр (дм)
     */
    const DECIMETER = 21;
    /**
     * Метр (м)
     */
    const METER = 22;
    /**
     * Квадратный сантиметр (кв.см)
     */
    const SQUARE_CENTIMETER = 30;
    /**
     * Квадратный дециметр (кв.дм)
     */
    const SQUARE_DECIMETER = 31;
    /**
     * Квадратный метр (кв.м)
     */
    const SQUARE_METER = 32;
    /**
     * Миллилитр (мл)
     */
    const MILLILITER = 40;
    /**
     * Литр (л)
     */
    const LITER = 41;
    /**
     * Кубический метр (куб.м)
     */
    const CUBIC_METER = 42;
    /**
     * Киловатт час (кВт.ч)
     */
    const KILOWATT_HOUR = 50;
    /**
     * Гигакалория (Гкал)
     */
    const GIGACALORIE = 51;
    /**
     * Сутки (день)
     */
    const DAY = 70;
    /**
     * Час (час)
     */
    const HOUR = 71;
    /**
     * Минута (мин)
     */
    const MINUTE = 72;
    /**
     * Секунда (с)
     */
    const SECOND = 73;
    /**
     * Килобайт (Кбайт)
     */
    const KILOBYTE = 80;
    /**
     * Мегабайт (Мбайт)
     */
    const MEGABYTE = 81;
    /**
     * Гигабайт (Гбайт)
     */
    const GIGABYTE = 82;
    /**
     * Терабайт (Тбайт)
     */
    const TERABYTE = 83;
    /**
     * Применяется при использовании иных единиц измерения
     */
    const OTHERS = 255;
}
