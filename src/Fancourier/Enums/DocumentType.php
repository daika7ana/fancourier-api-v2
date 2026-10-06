<?php

declare(strict_types=1);

namespace Fancourier\Enums;

/**
 * External AWB content type (`info.contentType` / documents only).
 */
enum DocumentType: string
{
    case Document = 'document';
    case NonDocument = 'non document';
}
