<?php

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
    private function makeRequest(string $method, array $payload, string $gateway = 'test/gateway'): AbstractRequest
    {
        $request = new class($gateway, $method, $payload) extends AbstractRequest {
            /** @var array<string, mixed> */
            public array $payload;

            public function __construct(string $gateway, string $method, array $payload)
            {
                parent::__construct();
                $this->gateway = $gateway;
                $this->method = $method;
                $this->payload = $payload;
            }

            public function pack()
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
        $this->assertSame(Fancourier::API_URL.'test/gateway?foo=bar', $client->lastUrl);
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
        $this->assertSame(Fancourier::API_URL.'test/gateway', $client->lastUrl);
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
        $this->assertSame(Fancourier::API_URL.'test/gateway?foo=bar', $client->lastUrl);
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
        $this->assertSame(Fancourier::API_URL.'test/gateway', $client->lastUrl);
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
        $this->assertSame(Fancourier::API_URL.'test/gateway?id=5', $client->lastUrl);
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
        $this->assertSame(Fancourier::API_URL.'test/gateway', $client->lastUrl);
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
        $this->assertSame('curl boom', $client->get_error());
    }
}
