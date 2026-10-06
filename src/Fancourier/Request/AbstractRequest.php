<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Fancourier;
use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Response\Generic;
use Fancourier\Response\ResponseInterface;
use Fancourier\RetryPolicy;
use Psr\Log\LoggerInterface;

abstract class AbstractRequest implements RequestInterface
{
    protected string $gateway = '';
    protected string $method = '';

    protected ?Auth $auth = null;

    protected Client $client;

    /** Empty string means "use {@see Fancourier::API_URL}". */
    protected string $baseUrl = '';

    /** @var array{verify: bool, timeout: bool} */
    protected array $clientOverrides = [
        'verify' => false,
        'timeout' => false,
    ];

    protected Generic $response;

    protected ?RetryPolicy $retryPolicy = null;
    protected ?LoggerInterface $logger = null;

    public function __construct()
    {
        $this->client = new Client();
        $this->response = new Generic();
    }

    #[\Override]
    public function authenticate(Auth $auth): static
    {
        $this->auth = $auth;

        return $this;
    }

    #[\Override]
    public function setVerify(bool $verifyHost = true, bool $verifyPeer = true): static
    {
        if ($this->clientOverrides['verify'] !== true) {
            $this->client->setVerify($verifyHost, $verifyPeer);
            $this->clientOverrides['verify'] = true;
        }

        return $this;
    }

    #[\Override]
    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = Fancourier::normalizeBaseUrl($baseUrl);

        return $this;
    }

    #[\Override]
    public function setClient(Client $client): static
    {
        $this->client = $client;
        // re-apply verify/timeout to the replacement transport
        $this->clientOverrides = ['verify' => false, 'timeout' => false];

        return $this;
    }

    #[\Override]
    public function setTimeout(int $conTimeout = 3, int $timeout = 6): static
    {
        if ($this->clientOverrides['timeout'] !== true) {
            $this->client->setTimeout($conTimeout, $timeout);
            $this->clientOverrides['timeout'] = true;
        }

        return $this;
    }

    #[\Override]
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    #[\Override]
    public function setRetryPolicy(?RetryPolicy $policy): static
    {
        $this->retryPolicy = $policy;

        return $this;
    }

    /**
     * @return Generic
     */
    #[\Override]
    public function send(): ResponseInterface
    {
        if ($this->auth === null) {
            throw new \RuntimeException('No Auth instance set; call authenticate() before send()');
        }

        $auth = $this->auth;

        if (empty($this->gateway)) {
            throw new \DomainException("No request gateway implemented");
        }

        if (empty($this->method)) {
            throw new \DomainException("No request method implemented");
        }

        $startedAt = microtime(true);

        // A reused request must not leak a previous attempt's error/data state.
        $this->response->reset();

        $data = $this->pack();

        // #27: remember whether the cached token was already stale before this
        // call, so a later transport failure can trigger one refresh + retry.
        $tokenWasExpired = $auth->isTokenExpired();

        $token = $auth->getToken();
        $this->assertUsableToken($token);

        // add authorization token
        $this->client->addHeader('Authorization', 'Bearer ' . $token);

        // Bounded transient-failure retry (opt-in through RetryPolicy). A 401 is
        // not in retryStatuses, so it is never transient-retried here.
        $maxAttempts = $this->retryPolicy === null ? 1 : max(1, $this->retryPolicy->maxAttempts);
        $responseString = false;
        $status = 0;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $responseString = $this->dispatch($data);
            $status = $this->client->getStatusCode();

            if (!$this->shouldRetry($responseString, $status, $attempt)) {
                break;
            }

            $this->sleepBeforeRetry($attempt);
        }

        // Refresh + retry exactly once when the request was rejected before it
        // executed (the single `if`, no loop, bounds it):
        //   - the server answered 401/403: it rejected our bearer even though
        //     the local expiry may still look valid (timezone skew), or
        //   - the transfer failed while the cached token was already stale.
        // A 401/403 means the request never ran, so retrying is safe even for
        // writes.
        // ponytail: expiry is otherwise approximated by a transport failure plus
        // a stale local token; upgrade path: parse the API's expiry signature
        // once documented and retry on that signal instead.
        $authRejected = $status === 401 || $status === 403;

        if ($authRejected || (false === $responseString && ($tokenWasExpired || $auth->isTokenExpired()))) {
            $token = $auth->getToken(true);
            $this->assertUsableToken($token);
            $this->client->addHeader('Authorization', 'Bearer ' . $token);
            $responseString = $this->dispatch($data);
            $status = $this->client->getStatusCode();
        }

        $httpStatus = $this->client->getStatusCode();
        $this->response->setHttpStatusCode($httpStatus > 0 ? $httpStatus : null);

        if (false === $responseString) {
            $this->response->setErrorCode(-1)->setErrorMessage($this->client->getError());
        } else {
            $this->response->setData($responseString);
        }

        $this->logCompletion($startedAt, $responseString, $httpStatus);

        return $this->response;
    }

    /**
     * Effective base URL for outgoing requests.
     */
    protected function baseUrl(): string
    {
        return $this->baseUrl !== '' ? $this->baseUrl : Fancourier::API_URL;
    }

    /**
     * Return the configured Auth instance, or fail loudly when a request is
     * built/sent before authenticate() was called.
     */
    protected function auth(): Auth
    {
        if ($this->auth === null) {
            throw new \RuntimeException('No Auth instance set; call authenticate() first');
        }

        return $this->auth;
    }

    /**
     * Whether another attempt is allowed for the just-finished one.
     */
    private function shouldRetry(string|false $responseString, int $status, int $attempt): bool
    {
        if ($this->retryPolicy === null || $attempt >= $this->retryPolicy->maxAttempts) {
            return false;
        }

        // A bare POST is non-idempotent: never retry unless explicitly allowed.
        if ($this->method === 'POST' && !$this->retryPolicy->retryNonIdempotent) {
            return false;
        }

        if ($responseString === false) {
            return true;
        }

        return in_array($status, $this->retryPolicy->retryStatuses, true);
    }

    /**
     * Exponential backoff before the next attempt. No policy or a zero base
     * delay means no sleep.
     */
    private function sleepBeforeRetry(int $attempt): void
    {
        if ($this->retryPolicy === null) {
            return;
        }

        $delayMs = (int) min(
            $this->retryPolicy->maxDelayMs,
            $this->retryPolicy->baseDelayMs * (2 ** ($attempt - 1)),
        );

        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }
    }

    /**
     * Record one lifecycle line per send(), without headers, tokens or bodies.
     */
    private function logCompletion(float $startedAt, string|false $responseString, int $httpStatus): void
    {
        if ($this->logger === null) {
            return;
        }

        $failed = false === $responseString || !$this->response->isOk();

        $context = [
            'method' => $this->method,
            'gateway' => $this->gateway,
            'http_status' => $httpStatus > 0 ? $httpStatus : null,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];

        if (false === $responseString) {
            $context['error'] = $this->client->getError();
        } elseif (!$this->response->isOk()) {
            $context['error'] = $this->response->getErrorMessage();
        }

        $this->logger->log(
            $failed ? 'error' : 'debug',
            'FAN Courier request completed',
            $context,
        );
    }

    /**
     * Refuse to perform an unauthenticated request when Auth cannot provide a
     * token (defect #26): without this guard send() would emit
     * 'Bearer ' . false and return an opaque API error.
     *
     * @param string|false $token
     */
    private function assertUsableToken(string|false $token): void
    {
        if (false === $token || $token === '') {
            $message = 'Authentication failed: no bearer token';
            $authMessage = $this->auth()->getTokenMessage();
            if ($authMessage !== '') {
                $message .= ': ' . $authMessage;
            }

            throw new \RuntimeException($message);
        }
    }

    /**
     * Send the packed payload over the transport selected by $this->method.
     *
     * @param array<array-key, mixed> $data
     * @return string|false
     */
    private function dispatch(array $data): string|false
    {
        if ($this->method == 'GET') {
            $get_params = http_build_query($data, '', '&');

            return $this->client->get($this->baseUrl() . $this->gateway . '?' . $get_params);
        } elseif ($this->method == 'POST') {
            return $this->client->postJson($this->baseUrl() . $this->gateway, $data);
        } elseif ($this->method == 'PUT') {
            $get_params = http_build_query($data, '', '&');

            return $this->client->setPutRequest(true)->get($this->baseUrl() . $this->gateway . '?' . $get_params);
        } elseif ($this->method == 'POSTPUT') {
            return $this->client->setPutRequest(true)->postMultiArray($this->baseUrl() . $this->gateway, $data);
        } elseif ($this->method == 'DELETE') {
            $get_params = http_build_query($data, '', '&');

            return $this->client->setDeleteRequest(true)->get($this->baseUrl() . $this->gateway . '?' . $get_params);
        } elseif ($this->method == 'POSTDELETE') {
            return $this->client->setDeleteRequest(true)->postMultiArray($this->baseUrl() . $this->gateway, $data);
        } else {
            throw new \DomainException("Unsupported request method: " . $this->method);
        }
    }

}
