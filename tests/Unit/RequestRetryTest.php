<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit;

use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Request\AbstractRequest;
use Fancourier\RetryPolicy;
use Fancourier\Tests\Support\ArrayLogger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Opt-in retry/backoff, reset-on-reuse and request logging, all offline.
 */
final class RequestRetryTest extends TestCase
{
    #[Test]
    public function a_transient_get_failure_is_retried_then_succeeds(): void
    {
        $client = $this->queuedClient([503, 200]);
        $request = $this->request('GET')
            ->setRetryPolicy(new RetryPolicy(maxAttempts: 3, baseDelayMs: 0));
        $request->setClient($client)->authenticate($this->auth());

        $response = $request->send();

        $this->assertSame(2, $client->calls);
        $this->assertTrue($response->isOk());
    }

    #[Test]
    public function a_transient_transport_failure_is_retried(): void
    {
        $client = $this->queuedClient([0, 200]);
        $request = $this->request('GET')
            ->setRetryPolicy(new RetryPolicy(maxAttempts: 3, baseDelayMs: 0));
        $request->setClient($client)->authenticate($this->auth());

        $response = $request->send();

        $this->assertSame(2, $client->calls);
        $this->assertTrue($response->isOk());
    }

    #[Test]
    public function a_permanent_transient_failure_stops_at_max_attempts(): void
    {
        $client = $this->queuedClient([503]);
        $request = $this->request('GET')
            ->setRetryPolicy(new RetryPolicy(maxAttempts: 3, baseDelayMs: 0));
        $request->setClient($client)->authenticate($this->auth());

        $request->send();

        $this->assertSame(3, $client->calls);
    }

    #[Test]
    public function a_post_is_not_retried_by_default(): void
    {
        $client = $this->queuedClient([503]);
        $request = $this->request('POST')
            ->setRetryPolicy(new RetryPolicy(maxAttempts: 3, baseDelayMs: 0));
        $request->setClient($client)->authenticate($this->auth());

        $request->send();

        $this->assertSame(1, $client->calls);
    }

    #[Test]
    public function a_post_is_retried_when_non_idempotent_retries_are_allowed(): void
    {
        $client = $this->queuedClient([503]);
        $request = $this->request('POST')
            ->setRetryPolicy(new RetryPolicy(maxAttempts: 3, baseDelayMs: 0, retryNonIdempotent: true));
        $request->setClient($client)->authenticate($this->auth());

        $request->send();

        $this->assertSame(3, $client->calls);
    }

    #[Test]
    public function no_policy_means_no_retry(): void
    {
        $client = $this->queuedClient([503]);
        $request = $this->request('GET');
        $request->setClient($client)->authenticate($this->auth());

        $request->send();

        $this->assertSame(1, $client->calls);
    }

    #[Test]
    public function a_reused_request_does_not_leak_a_previous_failure(): void
    {
        $client = $this->queuedClient([0, 200]);
        $request = $this->request('GET');
        $request->setClient($client)->authenticate($this->auth());

        $first = $request->send();
        $this->assertFalse($first->isOk());
        $this->assertSame(-1, $first->getErrorCode());

        $second = $request->send();

        $this->assertTrue($second->isOk());
        $this->assertNull($second->getErrorCode());
        $this->assertNull($second->getErrorMessage());
    }

    #[Test]
    public function a_completed_request_is_logged_without_secrets(): void
    {
        $logger = new ArrayLogger();
        $client = $this->queuedClient([200]);

        $request = $this->request('GET');
        $request->setClient($client)->setLogger($logger)->authenticate($this->auth());

        $request->send();

        $this->assertCount(1, $logger->records);
        $record = $logger->records[0];
        $this->assertSame('debug', $record['level']);
        $this->assertSame('GET', $record['context']['method']);
        $this->assertSame('test/gateway', $record['context']['gateway']);
        $this->assertSame(200, $record['context']['http_status']);
        $this->assertArrayHasKey('duration_ms', $record['context']);

        $encoded = (string) json_encode($logger->records);
        $this->assertStringNotContainsString('bearer-secret-token', $encoded);
        $this->assertStringNotContainsString('hunter2-password', $encoded);
    }

    #[Test]
    public function a_transport_failure_is_logged_at_error_level(): void
    {
        $logger = new ArrayLogger();
        $client = $this->queuedClient([0]);

        $request = $this->request('GET');
        $request->setClient($client)->setLogger($logger)->authenticate($this->auth());

        $request->send();

        $this->assertCount(1, $logger->records);
        $record = $logger->records[0];
        $this->assertSame('error', $record['level']);
        $this->assertSame('transport failure', $record['context']['error']);
    }

    private function request(string $method): AbstractRequest
    {
        return new class ($method) extends AbstractRequest {
            public function __construct(string $method)
            {
                parent::__construct();
                $this->gateway = 'test/gateway';
                $this->method = $method;
            }

            public function pack(): array
            {
                return ['foo' => 'bar'];
            }
        };
    }

    private function auth(): Auth
    {
        return new Auth(1, 'user', 'hunter2-password', 'bearer-secret-token');
    }

    /**
     * Transport double returning the queued statuses in order (last one
     * repeats). Status 0 simulates a transport failure.
     *
     * @param list<int> $statuses
     */
    private function queuedClient(array $statuses): Client
    {
        return new class ($statuses) extends Client {
            public int $calls = 0;

            private int $status = 0;

            /** @var list<int> */
            private array $statuses;

            /** @param list<int> $statuses */
            public function __construct(array $statuses)
            {
                parent::__construct();
                $this->statuses = $statuses;
            }

            public function addHeader(string $name, string $value): static
            {
                return $this;
            }

            public function get(string $url): string|false
            {
                $index = $this->calls++;
                $this->status = $this->statuses[$index]
                    ?? $this->statuses[count($this->statuses) - 1]
                    ?? 0;

                if ($this->status === 0) {
                    return false;
                }

                return $this->status >= 400 ? '{"status":"fail","message":"boom"}' : '{"ok":true}';
            }

            public function postJson(string $url, array $data): string|false
            {
                return $this->get($url);
            }

            public function getStatusCode(): int
            {
                return $this->status;
            }

            public function getError(): string
            {
                return 'transport failure';
            }
        };
    }
}
