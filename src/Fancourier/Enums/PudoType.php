<?php

namespace Fancourier\Enums;

/**
 * PUDO pickup-point type (`type`).
 */
enum PudoType: string
{
    case Fanbox = 'fanbox';
    case Paypoint = 'paypoint';
    case Office = 'office';
}
