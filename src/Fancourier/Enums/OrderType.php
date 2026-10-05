<?php

namespace Fancourier\Enums;

/**
 * Courier order type (`info.orderType`).
 */
enum OrderType: string
{
    case Standard = 'Standard';
    case ExpressLoco1h = 'Express Loco 1h';
    case ExpressLoco2h = 'Express Loco 2h';
    case ExpressLoco4h = 'Express Loco 4h';
    case ExpressLoco6h = 'Express Loco 6h';
}
