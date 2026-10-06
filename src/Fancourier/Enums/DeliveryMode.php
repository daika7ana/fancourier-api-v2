<?php

declare(strict_types=1);

namespace Fancourier\Enums;

/**
 * External AWB delivery mode (`info.deliveryMode`).
 */
enum DeliveryMode: string
{
    case Rutier = 'rutier';
    case Aerian = 'aerian';
}
