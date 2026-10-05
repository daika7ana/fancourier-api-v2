<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetCounties;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCountiesTest extends TestCase
{
    #[Test]
    public function it_packs_an_empty_payload(): void
    {
        $request = (new GetCounties())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));

        $this->assertSame([], $request->pack());
    }
}
