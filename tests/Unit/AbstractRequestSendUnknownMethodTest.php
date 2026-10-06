<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit;

use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Request\AbstractRequest;
use Fancourier\Tests\Support\FakeClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * send() must reject a method that none of its transport branches handle.
 */
final class AbstractRequestSendUnknownMethodTest extends TestCase
{
    #[Test]
    public function it_throws_on_an_unsupported_request_method(): void
    {
        $request = new class extends AbstractRequest {
            protected string $gateway = 'test/gateway';
            protected string $method = 'PATCH';

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

        $request->injectClient(new FakeClient());
        $request->authenticate(new Auth(1, 'u', 'p', 'token'));

        $this->expectException(\DomainException::class);

        $request->send();
    }
}
