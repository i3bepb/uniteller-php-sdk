<?php

namespace Tmconsulting\Uniteller\Common;

class BaseUri
{
    /**
     * Base uri без фискализации
     */
    const NON_RECEIPTED = 'https://wpay.uniteller.ru';
    /**
     * С фискализацией
     */
    const WITH_RECEIPTED = 'https://fpay.uniteller.ru';
    /**
     * Для бесконтактных систем оплаты MirPay, МТС Pay и др.
     */
    const FAST_PAYMENT = 'https://upay.uniteller.ru';
}
