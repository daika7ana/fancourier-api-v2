<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;

/**
 * Consumer-facing migration config: rename-only, no signature or type changes.
 * Run it to update calls to the old snake_case Client API at the 2.0 boundary.
 */
return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/examples',
    ])
    ->withConfiguredRule(RenameMethodRector::class, [
        new MethodCallRename('Fancourier\Client', 'set_verify', 'setVerify'),
        new MethodCallRename('Fancourier\Client', 'set_timeout', 'setTimeout'),
        new MethodCallRename('Fancourier\Client', 'set_put_request', 'setPutRequest'),
        new MethodCallRename('Fancourier\Client', 'set_delete_request', 'setDeleteRequest'),
        new MethodCallRename('Fancourier\Client', 'post_json', 'postJson'),
        new MethodCallRename('Fancourier\Client', 'post_ma', 'postMultiArray'),
        new MethodCallRename('Fancourier\Client', 'headers_add', 'addHeader'),
        new MethodCallRename('Fancourier\Client', 'headers_delete', 'deleteHeader'),
        new MethodCallRename('Fancourier\Client', 'get_error', 'getError'),
    ]);
