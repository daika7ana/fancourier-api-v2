<?php

declare(strict_types=1);

namespace Fancourier;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class Auth
{
    protected bool $verifyHost = true;
    protected bool $verifyPeer = true;

    protected int $timeout = 6;
    protected int $con_timeout = 3;

    protected string $gateway = 'login';

    /** Empty string means "use {@see Fancourier::API_URL}". */
    protected string $baseUrl = '';

    protected ?CacheInterface $cache = null;
    protected string $cacheKey = 'fancourier.token';

    protected ?LoggerInterface $logger = null;

    private int|string $clientId;
    private string $username;
    private string $password;

    private string $btoken = '';
    private string $btoken_expires_at = '';
    private string $btoken_message = '';

    public function __construct(int|string $clientId, string $username, string $password, string $token = '')
    {
        $this->clientId = $clientId;
        $this->username = $username;
        $this->password = $password;

        $this->btoken = $token;
        // if the token is empty, it will automatically retrieve it when performing a request
    }

    public function getClientId(): int|string
    {
        return $this->clientId;
    }

    public function getToken(bool $refresh = false): string|false
    {
        $this->btoken_message = '';
        if ($refresh || ($this->btoken == '') || $this->isTokenExpired()) {
            // An explicit refresh forces a fresh login; otherwise the shared
            // cache can satisfy the request without a round trip.
            if (!$refresh && $this->hydrateTokenFromCache()) {
                return $this->btoken;
            }

            try {
                $this->retrieve_token();
            } catch (\Exception $e) {
                $this->btoken_message = $e->getMessage();

                // Never includes credentials, only the failure reason.
                $this->logger?->warning('FAN Courier token retrieval failed', [
                    'error' => $e->getMessage(),
                ]);

                return false;
            }

            $this->storeTokenInCache();

            $this->logger?->debug('FAN Courier token retrieved', [
                'expires_at' => $this->btoken_expires_at,
            ]);
        }

        return $this->btoken;
    }

    /**
     * Attach a PSR-3 logger for token-lifecycle records.
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Share a PSR-16 cache holding the bearer token across processes.
     *
     * The effective key is scoped to the login credentials, so rotating to
     * another account (a different username/password) always misses the cache
     * and re-authenticates instead of reusing the previous account's token.
     * `clientId` is deliberately excluded: the token is issued for the
     * username/password pair, and clientId only scopes request payloads.
     */
    public function setTokenCache(CacheInterface $cache, string $key = 'fancourier.token'): static
    {
        $this->cache = $cache;
        $this->cacheKey = $key;

        return $this;
    }

    /**
     * Whether the stored token's expiry timestamp is in the past.
     *
     * The API returns `expiresAt` as 'Y-m-d H:i:s' with an unspecified timezone,
     * so the comparison uses the process timezone. A missing or unparsable
     * expiry is treated as "not expired" (conservative: keep using the cached
     * token instead of forcing a refresh), see defect #27.
     */
    public function isTokenExpired(): bool
    {
        if ($this->btoken_expires_at === '') {
            return false;
        }

        $expiresAt = strtotime($this->btoken_expires_at);
        if ($expiresAt === false) {
            return false;
        }

        return $expiresAt <= time();
    }

    public function getTokenMessage(): string
    {
        return $this->btoken_message;
    }

    public function getTokenExpiresAt(): string
    {
        return $this->btoken_expires_at; // Date format: Y-m-d H:i:s (unknown timezone)
    }


    public function setVerify(bool $host = true, bool $peer = true): static
    {
        $this->verifyHost = $host;
        $this->verifyPeer = $peer;

        return $this;
    }

    /**
     * Override the API base URL used for token retrieval (sandbox, proxy).
     * An empty string restores the package default.
     */
    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = Fancourier::normalizeBaseUrl($baseUrl);

        return $this;
    }

    public function setTimeout(int $con_timeout = 3, int $timeout = 6): static
    {
        $this->con_timeout = $con_timeout;
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Adopt a non-empty, unexpired token from the PSR-16 cache.
     *
     * @return bool true when the in-memory token was populated from the cache.
     */
    protected function hydrateTokenFromCache(): bool
    {
        if ($this->cache === null) {
            return false;
        }

        try {
            $cached = $this->cache->get($this->effectiveCacheKey());
        } catch (\Throwable) {
            return false;
        }

        // Never adopt a token that was cached for different credentials, even
        // if a caller forced the same base key for several accounts.
        if (!is_array($cached) || ($cached['fingerprint'] ?? null) !== $this->credentialFingerprint()) {
            return false;
        }

        $token = $cached['token'] ?? null;
        if (!is_string($token) || $token === '') {
            return false;
        }

        $expiresAt = $cached['expiresAt'] ?? null;
        $this->btoken = $token;
        $this->btoken_expires_at = is_string($expiresAt) ? $expiresAt : '';

        if ($this->isTokenExpired()) {
            $this->btoken = '';
            $this->btoken_expires_at = '';

            return false;
        }

        return true;
    }

    /**
     * Persist the freshly retrieved token; a broken cache must never break auth.
     */
    protected function storeTokenInCache(): void
    {
        if ($this->cache === null) {
            return;
        }

        try {
            $this->cache->set($this->effectiveCacheKey(), [
                'token' => $this->btoken,
                'expiresAt' => $this->btoken_expires_at,
                'fingerprint' => $this->credentialFingerprint(),
            ], $this->cacheTtl());
        } catch (\Throwable) {
            // Ignore: authentication already succeeded without the cache.
        }
    }

    /**
     * Stable fingerprint of the login credentials.
     *
     * Authentication uses only the username/password pair (clientId is not part
     * of the token's identity), so rotating either credential changes this
     * fingerprint. Only a hash is stored, never the plaintext password.
     */
    protected function credentialFingerprint(): string
    {
        return hash('sha256', \strlen($this->username) . ':' . $this->username . $this->password);
    }

    /**
     * Cache slot for these credentials; different accounts never collide.
     *
     * Uses '.' rather than ':' because PSR-16 reserves ':' (and {}()/\@) in keys.
     */
    protected function effectiveCacheKey(): string
    {
        return $this->cacheKey . '.' . $this->credentialFingerprint();
    }

    /**
     * Seconds until expiry for the cache TTL, or null when unknown.
     */
    protected function cacheTtl(): ?int
    {
        if ($this->btoken_expires_at === '') {
            return null;
        }

        $expiresAt = strtotime($this->btoken_expires_at);
        if ($expiresAt === false) {
            return null;
        }

        return max(1, $expiresAt - time());
    }

    // Widened private -> protected so the token path is unit-testable without
    // network (a test subclass can override the retrieval).
    protected function retrieve_token(): bool
    {
        $client = new Client();
        $client->setVerify($this->verifyHost, $this->verifyPeer);
        $client->setTimeout($this->con_timeout, $this->timeout);

        $url = ($this->baseUrl !== '' ? $this->baseUrl : Fancourier::API_URL) . $this->gateway;

        $data = [
            'username' => $this->username,
            'password' => $this->password,
        ];
        $response = $client->post($url, $data);
        // {"status":"success","data":{"token":"48944740|EJU0MgzeWY4y1zy9JpQg3cu3cDiqoVg1ZXsIrqLQ","expiresAt":"2024-06-11 07:23:53"}}

        if ($response === false) {
            throw new \Exception("Server error: " . $client->getError());
        }

        $response_json = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if ($response_json['status'] == 'success') {
                $this->btoken = $response_json['data']['token'];
                $this->btoken_expires_at = $response_json['data']['expiresAt'];
            } else {
                throw new \Exception("Login failed: " . $response_json['message']);
            }
        } else {
            throw new \Exception("Server error: invalid response: " . $response);
        }

        unset($client);

        return ($this->btoken !== '');
    }
}
