<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Response\ResponseInterface;
use Fancourier\RetryPolicy;
use Psr\Log\LoggerInterface;

interface RequestInterface
{
    public function authenticate(Auth $auth): static;
    public function setVerify(bool $verifyHost = true, bool $verifyPeer = true): static;
    public function setTimeout(int $conTimeout = 3, int $timeout = 6): static;

    /**
     * Override the API base URL (sandbox, proxy, mock server).
     * An empty string restores the package default.
     */
    public function setBaseUrl(string $baseUrl): static;

    /**
     * Replace the transport. Useful to inject a fake/custom HTTP client.
     */
    public function setClient(Client $client): static;

    /**
     * Attach a PSR-3 logger for request lifecycle records.
     */
    public function setLogger(LoggerInterface $logger): static;

    /**
     * Enable retry/backoff for transient failures. `null` disables retries.
     */
    public function setRetryPolicy(?RetryPolicy $policy): static;

    public function send(): ResponseInterface;

    /** @return array<array-key, mixed> */
    public function pack(): array;
}
