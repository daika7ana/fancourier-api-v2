<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit;

use Fancourier\Auth;
use Fancourier\Tests\Support\ArrayCache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Offline tests for the PSR-16 bearer-token cache.
 */
class AuthTokenCacheTest extends TestCase
{
    #[Test]
    public function the_first_token_retrieval_populates_the_cache(): void
    {
        $cache = new ArrayCache();
        $auth = $this->retrievingAuth();
        $auth->setTokenCache($cache);

        $this->assertSame('retrieved-token', $auth->getToken());
        $this->assertSame(1, $auth->retrieveCalls);

        $cached = $cache->get($this->key());
        $this->assertIsArray($cached);
        $this->assertSame('retrieved-token', $cached['token']);
        $this->assertNotEmpty($cached['expiresAt']);
        $this->assertSame($this->fingerprint(), $cached['fingerprint']);
    }

    #[Test]
    public function a_shared_cache_hydrates_without_retrieving(): void
    {
        $cache = new ArrayCache();
        $cache->set($this->key(), [
            'token' => 'cached-token',
            'expiresAt' => date('Y-m-d H:i:s', time() + 3600),
            'fingerprint' => $this->fingerprint(),
        ]);

        $auth = $this->retrievingAuth();
        $auth->setTokenCache($cache);

        $this->assertSame('cached-token', $auth->getToken());
        $this->assertSame(0, $auth->retrieveCalls);
    }

    /**
     * Rotating to another account (a different username) must never reuse the
     * previous account's token.
     */
    #[Test]
    public function another_account_does_not_reuse_the_cached_token(): void
    {
        $cache = new ArrayCache();

        $authA = $this->retrievingAuth(1, 'user-a');
        $authA->setTokenCache($cache);
        $this->assertSame('retrieved-token', $authA->getToken());
        $this->assertSame(1, $authA->retrieveCalls);

        $authB = $this->retrievingAuth(1, 'user-b');
        $authB->setTokenCache($cache);

        $this->assertSame('retrieved-token', $authB->getToken());
        $this->assertSame(1, $authB->retrieveCalls);
    }

    #[Test]
    public function a_rotated_password_does_not_reuse_the_cached_token(): void
    {
        $cache = new ArrayCache();

        $authA = $this->retrievingAuth(1, 'u', 'old-pass');
        $authA->setTokenCache($cache);
        $authA->getToken();

        $authB = $this->retrievingAuth(1, 'u', 'new-pass');
        $authB->setTokenCache($cache);

        $this->assertSame('retrieved-token', $authB->getToken());
        $this->assertSame(1, $authB->retrieveCalls);
    }

    /**
     * clientId is not part of the token's identity, so the same login shares
     * one cache slot across different client ids.
     */
    #[Test]
    public function the_client_id_does_not_scope_the_cache(): void
    {
        $cache = new ArrayCache();

        $authA = $this->retrievingAuth(1, 'u', 'p');
        $authA->setTokenCache($cache);
        $authA->getToken();

        $authB = $this->retrievingAuth(2, 'u', 'p');
        $authB->setTokenCache($cache);

        $this->assertSame('retrieved-token', $authB->getToken());
        $this->assertSame(0, $authB->retrieveCalls);
    }

    /**
     * A foreign token written under our own key is rejected by the credential
     * fingerprint (defence against a shared/overwritten cache slot).
     */
    #[Test]
    public function a_cached_token_for_another_account_is_ignored_even_under_the_same_key(): void
    {
        $cache = new ArrayCache();
        $cache->set($this->key(), [
            'token' => 'foreign-token',
            'expiresAt' => date('Y-m-d H:i:s', time() + 3600),
            'fingerprint' => $this->fingerprint('user-b', 'p'),
        ]);

        $auth = $this->retrievingAuth();
        $auth->setTokenCache($cache);

        $this->assertSame('retrieved-token', $auth->getToken());
        $this->assertSame(1, $auth->retrieveCalls);
    }

    #[Test]
    public function an_expired_cached_token_triggers_a_retrieval(): void
    {
        $cache = new ArrayCache();
        $cache->set($this->key(), [
            'token' => 'stale-token',
            'expiresAt' => date('Y-m-d H:i:s', time() - 60),
            'fingerprint' => $this->fingerprint(),
        ]);

        $auth = $this->retrievingAuth();
        $auth->setTokenCache($cache);

        $this->assertSame('retrieved-token', $auth->getToken());
        $this->assertSame(1, $auth->retrieveCalls);
    }

    #[Test]
    public function an_explicit_refresh_bypasses_a_valid_cache(): void
    {
        $cache = new ArrayCache();
        $cache->set($this->key(), [
            'token' => 'cached-token',
            'expiresAt' => date('Y-m-d H:i:s', time() + 3600),
            'fingerprint' => $this->fingerprint(),
        ]);

        $auth = $this->retrievingAuth();
        $auth->setTokenCache($cache);

        $this->assertSame('retrieved-token', $auth->getToken(true));
        $this->assertSame(1, $auth->retrieveCalls);
    }

    #[Test]
    public function a_throwing_cache_does_not_break_authentication(): void
    {
        $cache = new class extends ArrayCache {
            public function get(string $key, mixed $default = null): mixed
            {
                throw new \RuntimeException('cache get boom');
            }

            public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
            {
                throw new \RuntimeException('cache set boom');
            }
        };

        $auth = $this->retrievingAuth();
        $auth->setTokenCache($cache);

        $this->assertSame('retrieved-token', $auth->getToken());
        $this->assertSame(1, $auth->retrieveCalls);
    }

    private function key(string $username = 'u', string $password = 'p'): string
    {
        return 'fancourier.token.' . $this->fingerprint($username, $password);
    }

    private function fingerprint(string $username = 'u', string $password = 'p'): string
    {
        return hash('sha256', \strlen($username) . ':' . $username . $password);
    }

    private function retrievingAuth(int|string $clientId = 1, string $username = 'u', string $password = 'p'): Auth
    {
        return new class ($clientId, $username, $password, '') extends Auth {
            public int $retrieveCalls = 0;

            protected function retrieve_token(): bool
            {
                $this->retrieveCalls++;

                (new \ReflectionProperty(Auth::class, 'btoken'))->setValue($this, 'retrieved-token');
                (new \ReflectionProperty(Auth::class, 'btoken_expires_at'))
                    ->setValue($this, date('Y-m-d H:i:s', time() + 3600));

                return true;
            }
        };
    }
}
