<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetCountiesExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCountiesExternalTest extends TestCase
{
    private function request(): GetCountiesExternal
    {
        return (new GetCountiesExternal())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_query_by_default(): void
    {
        $this->assertSame([], $this->request()->pack());
    }

    #[Test]
    public function it_packs_the_country_when_set(): void
    {
        $this->assertSame(['country' => 'RO'], $this->request()->setCountry('RO')->pack());
    }
}
