<?php

declare(strict_types=1);

namespace Fancourier\Tests\Support;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * In-memory PSR-3 logger capturing level/message/context for assertions.
 */
final class ArrayLogger implements LoggerInterface
{
    use LoggerTrait;

    /** @var list<array{level: string, message: string, context: array<string, mixed>}> */
    public array $records = [];

    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => (string) $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}
