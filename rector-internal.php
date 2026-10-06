<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

/**
 * Internal-only config, not part of the consumer migration story. It adds
 * `declare(strict_types=1)` and is exercised in the later strict-types wave.
 */
return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/examples',
    ])
    ->withRules([
        DeclareStrictTypesRector::class,
    ]);
