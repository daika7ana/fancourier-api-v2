<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetCities;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCitiesTest extends TestCase
{
    private function request(): GetCities
    {
        return (new GetCities())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_query_by_default(): void
    {
        $this->assertSame([], $this->request()->pack());
    }

    #[Test]
    public function it_packs_the_county_when_set(): void
    {
        $this->assertSame(['county' => 'Cluj'], $this->request()->setCounty('Cluj')->pack());
    }
}
