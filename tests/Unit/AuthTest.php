<?php

namespace Fancourier\Tests\Unit;

use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Request\AbstractRequest;
use Fancourier\Tests\Support\FakeClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Offline tests for the Auth token lifecycle.
 */
final class AuthTest extends TestCase
{
    private function setExpiry(Auth $auth, string $expiresAt): void
    {
        $property = new \ReflectionProperty(Auth::class, 'btoken_expires_at');
        $property->setAccessible(true);
        $property->setValue($auth, $expiresAt);
    }

    /**
     * @return Auth the anonymous test subclass, with its public counters
     */
    private function retrievingAuth(string $token): Auth
    {
        return new class(1, 'u', 'p', $token) extends Auth {
            public int $retrieveCalls = 0;

            protected function retrieve_token(): bool
            {
                $this->retrieveCalls++;

                return true;
            }
        };
    }

    #[Test]
    public function get_token_refreshes_when_the_stored_token_is_expired(): void
    {
        $auth = $this->retrievingAuth('cached-token');
        $this->setExpiry($auth, date('Y-m-d H:i:s', time() - 3600));

        $this->assertSame('cached-token', $auth->getToken());
        $this->assertSame(1, $auth->retrieveCalls);
    }

    #[Test]
    public function get_token_keeps_a_token_that_is_still_valid(): void
    {
        $auth = $this->retrievingAuth('cached-token');
        $this->setExpiry($auth, date('Y-m-d H:i:s', time() + 3600));

        $this->assertSame('cached-token', $auth->getToken());
        $this->assertSame(0, $auth->retrieveCalls);
    }

    #[Test]
    public function get_token_without_a_stored_token_retrieves_one(): void
    {
        $auth = $this->retrievingAuth('');

        $auth->getToken();

        $this->assertSame(1, $auth->retrieveCalls);
    }

    #[Test]
    public function an_expired_token_is_reported_as_expired(): void
    {
        $auth = new Auth(1, 'u', 'p', 'tok');
        $this->setExpiry($auth, date('Y-m-d H:i:s', time() - 60));

        $this->assertTrue($auth->isTokenExpired());
    }

    #[Test]
    public function a_future_expiry_is_not_expired(): void
    {
        $auth = new Auth(1, 'u', 'p', 'tok');
        $this->setExpiry($auth, date('Y-m-d H:i:s', time() + 60));

        $this->assertFalse($auth->isTokenExpired());
    }

    #[Test]
    public function an_unparsable_expiry_is_treated_as_not_expired(): void
    {
        $auth = new Auth(1, 'u', 'p', 'tok');
        $this->setExpiry($auth, 'not-a-date');

        $this->assertFalse($auth->isTokenExpired());
    }

    #[Test]
    public function a_missing_expiry_is_treated_as_not_expired(): void
    {
        $auth = new Auth(1, 'u', 'p', 'tok');

        $this->assertFalse($auth->isTokenExpired());
    }

    /**
     * #26: Auth returning false must make send() throw before any HTTP call.
     */
    #[Test]
    public function send_throws_and_makes_no_http_call_when_auth_has_no_token(): void
    {
        $auth = new class(1, 'u', 'p') extends Auth {
            public function getToken(bool $refresh = false): string|false
            {
                return false;
            }

            public function getTokenMessage(): string
            {
                return 'login failed';
            }
        };

        $request = new class extends AbstractRequest {
            protected string $gateway = 'test/gateway';
            protected string $method = 'POST';

            public function pack(): array
            {
                return [];
            }

            public function injectClient(Client $client): static
            {
                $this->client = $client;

                return $this;
            }
        };

        $client = new FakeClient();
        $request->injectClient($client);
        $request->authenticate($auth);

        try {
            $request->send();
            $this->fail('Expected a RuntimeException when no token is available');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('no bearer token', $e->getMessage());
            $this->assertStringContainsString('login failed', $e->getMessage());
        }

        $this->assertSame('', $client->lastCall, 'No HTTP call may happen without a token');
    }

    #[Test]
    public function send_throws_when_auth_returns_an_empty_token(): void
    {
        $auth = new class(1, 'u', 'p') extends Auth {
            public function getToken(bool $refresh = false): string|false
            {
                return '';
            }
        };

        $request = new class extends AbstractRequest {
            protected string $gateway = 'test/gateway';
            protected string $method = 'GET';

            public function pack(): array
            {
                return [];
            }

            public function injectClient(Client $client): static
            {
                $this->client = $client;

                return $this;
            }
        };

        $client = new FakeClient();
        $request->injectClient($client);
        $request->authenticate($auth);

        $this->expectException(\RuntimeException::class);

        try {
            $request->send();
        } finally {
            $this->assertSame('', $client->lastCall);
        }
    }
}
