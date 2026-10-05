<?php

namespace Fancourier\Enums;

/**
 * External AWB delivery mode (`info.deliveryMode`).
 */
enum DeliveryMode: string
{
    case Rutier = 'rutier';
    case Aerian = 'aerian';
}
