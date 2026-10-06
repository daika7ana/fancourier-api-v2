<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetCitiesExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCitiesExternalTest extends TestCase
{
    #[Test]
    public function it_packs_the_default_page_size(): void
    {
        $this->assertSame(['perPage' => 100], $this->request()->pack());
    }

    #[Test]
    public function it_returns_the_configured_page_and_per_page(): void
    {
        $request = $this->request()->setPage(3)->setPerPage(25);

        $this->assertSame(3, $request->getPage());
        $this->assertSame(25, $request->getPerPage());
    }

    #[Test]
    public function it_packs_all_populated_filters(): void
    {
        $packed = $this->request()
            ->setCountry('RO')
            ->setCounty('Cluj')
            ->setPage(2)
            ->setPerPage(50)
            ->pack();

        $this->assertSame(
            ['country' => 'RO', 'county' => 'Cluj', 'page' => 2, 'perPage' => 50],
            $packed,
        );
    }

    #[Test]
    public function it_caps_the_page_size_at_one_hundred(): void
    {
        $this->assertSame(['perPage' => 100], $this->request()->setPerPage(500)->pack());
    }
    private function request(): GetCitiesExternal
    {
        return (new GetCitiesExternal())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }
}
