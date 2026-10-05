<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetServices;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetServicesTest extends TestCase
{
    #[Test]
    public function it_packs_an_empty_payload(): void
    {
        $request = (new GetServices())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));

        $this->assertSame([], $request->pack());
    }
}
