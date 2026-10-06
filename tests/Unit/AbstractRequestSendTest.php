<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit;

use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Fancourier;
use Fancourier\Request\AbstractRequest;
use Fancourier\Tests\Support\FakeClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Exercises AbstractRequest::send() offline through a test-only Client double.
 */
final class AbstractRequestSendTest extends TestCase
{
    #[Test]
    public function it_sends_a_get_request_as_a_query_string(): void
    {
        $client = (new FakeClient())->setResponse('{"ok":true}');
        $request = $this->makeRequest('GET', ['foo' => 'bar'])->injectClient($client);
        $request->authenticate($this->auth());

        $response = $request->send();

        $this->assertSame('get', $client->lastCall);
        $this->assertFalse($client->isPut);
        $this->assertFalse($client->isDelete);
        $this->assertSame(Fancourier::API_URL . 'test/gateway?foo=bar', $client->lastUrl);
        $this->assertSame('Bearer test-token', $client->lastAuthorization);
        $this->assertTrue($response->isOk());
        $this->assertSame('{"ok":true}', $response->getData());
    }

    #[Test]
    public function it_sends_a_post_request_as_json(): void
    {
        $client = new FakeClient();
        $request = $this->makeRequest('POST', ['foo' => 'bar'])->injectClient($client);
        $request->authenticate($this->auth());

        $response = $request->send();

        $this->assertSame('post_json', $client->lastCall);
        $this->assertSame(Fancourier::API_URL . 'test/gateway', $client->lastUrl);
        $this->assertSame(['foo' => 'bar'], $client->lastData);
        $this->assertTrue($response->isOk());
    }

    #[Test]
    public function it_sends_a_put_request_through_the_put_flagged_get(): void
    {
        $client = new FakeClient();
        $request = $this->makeRequest('PUT', ['foo' => 'bar'])->injectClient($client);
        $request->authenticate($this->auth());

        $request->send();

        $this->assertSame('get', $client->lastCall);
        $this->assertTrue($client->isPut);
        $this->assertFalse($client->isDelete);
        $this->assertSame(Fancourier::API_URL . 'test/gateway?foo=bar', $client->lastUrl);
    }

    #[Test]
    public function it_sends_a_postput_request_through_the_put_flagged_post_ma(): void
    {
        $client = new FakeClient();
        $payload = ['outer' => ['inner' => 1]];
        $request = $this->makeRequest('POSTPUT', $payload)->injectClient($client);
        $request->authenticate($this->auth());

        $request->send();

        $this->assertSame('post_ma', $client->lastCall);
        $this->assertTrue($client->isPut);
        $this->assertFalse($client->isDelete);
        $this->assertSame(Fancourier::API_URL . 'test/gateway', $client->lastUrl);
        $this->assertSame($payload, $client->lastData);
    }

    #[Test]
    public function it_sends_a_delete_request_through_the_delete_flagged_get(): void
    {
        $client = new FakeClient();
        $request = $this->makeRequest('DELETE', ['id' => 5])->injectClient($client);
        $request->authenticate($this->auth());

        $request->send();

        $this->assertSame('get', $client->lastCall);
        $this->assertFalse($client->isPut);
        $this->assertTrue($client->isDelete);
        $this->assertSame(Fancourier::API_URL . 'test/gateway?id=5', $client->lastUrl);
    }

    #[Test]
    public function it_sends_a_postdelete_request_through_the_delete_flagged_post_ma(): void
    {
        $client = new FakeClient();
        $request = $this->makeRequest('POSTDELETE', ['id' => 5])->injectClient($client);
        $request->authenticate($this->auth());

        $request->send();

        $this->assertSame('post_ma', $client->lastCall);
        $this->assertFalse($client->isPut);
        $this->assertTrue($client->isDelete);
        $this->assertSame(Fancourier::API_URL . 'test/gateway', $client->lastUrl);
    }

    #[Test]
    public function a_false_transport_response_surfaces_as_an_error(): void
    {
        $client = (new FakeClient())->setFailure('curl boom');
        $request = $this->makeRequest('GET', [])->injectClient($client);
        $request->authenticate($this->auth());

        $response = $request->send();

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('curl boom', $response->getErrorMessage());
        $this->assertSame('curl boom', $client->getError());
    }

    /**
     * #11 (superseding decision): an empty body is a failure, and send() must
     * report a non-empty error message rather than claiming success.
     */
    #[Test]
    public function an_empty_response_body_surfaces_as_a_non_empty_error(): void
    {
        $client = (new FakeClient())->setFailure('FAN Courier returned an empty response');
        $request = $this->makeRequest('POST', [])->injectClient($client);
        $request->authenticate($this->auth());

        $response = $request->send();

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('FAN Courier returned an empty response', $response->getErrorMessage());
    }

    /**
     * #27: a transport failure against a stale token triggers one refresh and
     * exactly one retry; the retry's body wins.
     */
    #[Test]
    public function it_refreshes_an_expired_token_and_retries_exactly_once(): void
    {
        $auth = new class (1, 'u', 'p') extends Auth {
            public int $refreshCalls = 0;

            public function getToken(bool $refresh = false): string|false
            {
                if ($refresh) {
                    $this->refreshCalls++;

                    return 'fresh-token';
                }

                return 'stale-token';
            }

            public function isTokenExpired(): bool
            {
                return true;
            }
        };

        $client = new class extends Client {
            public int $calls = 0;

            /** @var list<string> */
            public array $authHeaders = [];

            public function addHeader(string $name, string $value): static
            {
                if (strtolower((string) $name) === 'authorization') {
                    $this->authHeaders[] = (string) $value;
                }

                return $this;
            }

            public function postJson(string $url, array $data): string|false
            {
                $this->calls++;

                return $this->calls === 1 ? false : '{"ok":true}';
            }

            public function getError(): string
            {
                return 'transient';
            }
        };

        $request = $this->makeRequest('POST', ['foo' => 'bar'])->injectClient($client);
        $request->authenticate($auth);

        $response = $request->send();

        $this->assertSame(2, $client->calls);
        $this->assertSame(1, $auth->refreshCalls);
        $this->assertSame(['Bearer stale-token', 'Bearer fresh-token'], $client->authHeaders);
        $this->assertTrue($response->isOk());
        $this->assertSame('{"ok":true}', $response->getData());
    }

    /**
     * #27: the retry is bounded — a second failure is surfaced, not retried
     * again (no infinite refresh loop).
     */
    #[Test]
    public function it_does_not_retry_more_than_once_when_the_retry_also_fails(): void
    {
        $auth = new class (1, 'u', 'p') extends Auth {
            public int $refreshCalls = 0;

            public function getToken(bool $refresh = false): string|false
            {
                if ($refresh) {
                    $this->refreshCalls++;

                    return 'fresh-token';
                }

                return 'stale-token';
            }

            public function isTokenExpired(): bool
            {
                return true;
            }
        };

        $client = new class extends Client {
            public int $calls = 0;

            public function addHeader(string $name, string $value): static
            {
                return $this;
            }

            public function postJson(string $url, array $data): string|false
            {
                $this->calls++;

                return false;
            }

            public function getError(): string
            {
                return 'still down';
            }
        };

        $request = $this->makeRequest('POST', [])->injectClient($client);
        $request->authenticate($auth);

        $response = $request->send();

        $this->assertSame(2, $client->calls);
        $this->assertSame(1, $auth->refreshCalls);
        $this->assertFalse($response->isOk());
        $this->assertSame('still down', $response->getErrorMessage());
    }

    /**
     * #27: without a stale token a transport failure is surfaced as today and
     * no token refresh is attempted.
     */
    #[Test]
    public function it_does_not_retry_when_the_token_is_not_expired(): void
    {
        $auth = new class (1, 'u', 'p', 'fresh-token') extends Auth {
            public int $refreshCalls = 0;

            public function getToken(bool $refresh = false): string|false
            {
                if ($refresh) {
                    $this->refreshCalls++;
                }

                return 'fresh-token';
            }

            public function isTokenExpired(): bool
            {
                return false;
            }
        };

        $client = new class extends Client {
            public int $calls = 0;

            public function addHeader(string $name, string $value): static
            {
                return $this;
            }

            public function postJson(string $url, array $data): string|false
            {
                $this->calls++;

                return false;
            }

            public function getError(): string
            {
                return 'curl boom';
            }
        };

        $request = $this->makeRequest('POST', [])->injectClient($client);
        $request->authenticate($auth);

        $response = $request->send();

        $this->assertSame(1, $client->calls);
        $this->assertSame(0, $auth->refreshCalls);
        $this->assertFalse($response->isOk());
        $this->assertSame('curl boom', $response->getErrorMessage());
    }
    #[Test]
    public function it_uses_a_custom_base_url_when_set(): void
    {
        $client = (new FakeClient())->setResponse('{"ok":true}');
        $request = $this->makeRequest('GET', ['foo' => 'bar'])
            ->injectClient($client)
            ->setBaseUrl('https://sandbox.example');
        $request->authenticate($this->auth());

        $request->send();

        $this->assertSame('https://sandbox.example/test/gateway?foo=bar', $client->lastUrl);
    }

    #[Test]
    public function it_uses_a_client_injected_through_set_client(): void
    {
        $client = (new FakeClient())->setResponse('{"ok":true}');
        $request = $this->makeRequest('GET', ['foo' => 'bar']);
        $request->authenticate($this->auth());
        $request->setClient($client);

        $response = $request->send();

        $this->assertSame('get', $client->lastCall);
        $this->assertSame('https://api.fancourier.ro/test/gateway?foo=bar', $client->lastUrl);
        $this->assertTrue($response->isOk());
    }

    #[Test]
    public function it_exposes_the_http_status_on_the_response(): void
    {
        $client = new class extends Client {
            public function get(string $url): string|false
            {
                return '{"ok":true}';
            }

            public function getStatusCode(): int
            {
                return 200;
            }
        };

        $request = $this->makeRequest('GET', [])->injectClient($client);
        $request->authenticate($this->auth());

        $response = $request->send();

        $this->assertSame(200, $response->getHttpStatusCode());
    }

    /**
     * #27 (P0): the server rejects the bearer with 401 even though the local
     * expiry still looks valid (timezone skew). send() must refresh and retry
     * exactly once.
     */
    #[Test]
    public function it_refreshes_and_retries_when_the_api_rejects_the_bearer_with_401(): void
    {
        $auth = new class (1, 'u', 'p', 'stale-token') extends Auth {
            public int $refreshCalls = 0;

            public function getToken(bool $refresh = false): string|false
            {
                if ($refresh) {
                    $this->refreshCalls++;

                    return 'fresh-token';
                }

                return 'stale-token';
            }

            public function isTokenExpired(): bool
            {
                return false;
            }
        };

        $client = new class extends Client {
            public int $calls = 0;
            public int $status = 0;

            /** @var list<string> */
            public array $authHeaders = [];

            /** @var list<int> */
            private array $statuses = [401, 200];

            public function addHeader(string $name, string $value): static
            {
                if (strtolower((string) $name) === 'authorization') {
                    $this->authHeaders[] = (string) $value;
                }

                return $this;
            }

            public function postJson(string $url, array $data): string|false
            {
                $this->calls++;
                $this->status = $this->statuses[$this->calls - 1] ?? 200;

                return $this->calls === 1
                    ? '{"status":"fail","message":"Unauthorized"}'
                    : '{"ok":true}';
            }

            public function getStatusCode(): int
            {
                return $this->status;
            }
        };

        $request = $this->makeRequest('POST', ['foo' => 'bar'])->injectClient($client);
        $request->authenticate($auth);

        $response = $request->send();

        $this->assertSame(2, $client->calls);
        $this->assertSame(1, $auth->refreshCalls);
        $this->assertSame(['Bearer stale-token', 'Bearer fresh-token'], $client->authHeaders);
        $this->assertTrue($response->isOk());
        $this->assertSame('{"ok":true}', $response->getData());
    }

    /**
     * #27 (P0): a non-auth HTTP error (e.g. validation 422) must be surfaced as
     * the API body, not trigger a pointless token refresh.
     */
    #[Test]
    public function it_does_not_retry_a_non_auth_http_error(): void
    {
        $client = new class extends Client {
            public int $calls = 0;

            public function postJson(string $url, array $data): string|false
            {
                $this->calls++;

                return '{"status":"fail","message":"Validation error"}';
            }

            public function getStatusCode(): int
            {
                return 422;
            }
        };

        $request = $this->makeRequest('POST', [])->injectClient($client);
        $request->authenticate($this->auth());

        $response = $request->send();

        $this->assertSame(1, $client->calls);
        $this->assertSame('{"status":"fail","message":"Validation error"}', $response->getData());
    }

    private function makeRequest(string $method, array $payload, string $gateway = 'test/gateway'): AbstractRequest
    {
        $request = new class ($gateway, $method, $payload) extends AbstractRequest {
            /** @var array<string, mixed> */
            public array $payload;

            public function __construct(string $gateway, string $method, array $payload)
            {
                parent::__construct();
                $this->gateway = $gateway;
                $this->method = $method;
                $this->payload = $payload;
            }

            public function pack(): array
            {
                return $this->payload;
            }

            public function injectClient(Client $client): static
            {
                $this->client = $client;

                return $this;
            }
        };

        return $request;
    }

    private function auth(): Auth
    {
        return new Auth(12345, 'user', 'pass', 'test-token');
    }
}
