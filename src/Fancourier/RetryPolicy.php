<?php

declare(strict_types=1);

namespace Fancourier;

/**
 * Immutable opt-in retry configuration for the request path.
 *
 * A request retries a failed transport or a response whose HTTP status is in
 * {@see $retryStatuses}, up to {@see $maxAttempts}, with exponential backoff.
 * Bare POST requests are only retried when {@see $retryNonIdempotent} is true.
 */
final class RetryPolicy
{
    /** @param list<int> $retryStatuses */
    public function __construct(
        public readonly int $maxAttempts = 3,
        public readonly int $baseDelayMs = 200,
        public readonly int $maxDelayMs = 5000,
        public readonly array $retryStatuses = [429, 500, 502, 503, 504],
        public readonly bool $retryNonIdempotent = false,
    ) {}
}
