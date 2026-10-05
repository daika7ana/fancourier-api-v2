<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetStreets;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetStreetsTest extends TestCase
{
    private function request(): GetStreets
    {
        return (new GetStreets())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_the_default_page_size(): void
    {
        $this->assertSame(['perPage' => 1000], $this->request()->pack());
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
            ->setCounty('Cluj')
            ->setCity('Cluj-Napoca')
            ->setPage(2)
            ->setPerPage(500)
            ->pack();

        $this->assertSame(
            ['county' => 'Cluj', 'locality' => 'Cluj-Napoca', 'page' => 2, 'perPage' => 500],
            $packed
        );
    }

    #[Test]
    public function it_caps_the_page_size_at_one_thousand(): void
    {
        $this->assertSame(['perPage' => 1000], $this->request()->setPerPage(5000)->pack());
    }
}
