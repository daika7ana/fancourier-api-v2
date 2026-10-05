<?php

namespace Fancourier\Enums;

/**
 * AWB payment type (`info.payment` / `info.returnPayment`).
 */
enum PaymentType: string
{
    case Expeditor = 'expeditor';
    case Destinatar = 'destinatar';
    case Altul = 'Altul';
}
