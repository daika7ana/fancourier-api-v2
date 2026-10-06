<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetShippingSlip;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetShippingSlipTest extends TestCase
{
    #[Test]
    public function it_packs_todays_date_and_the_default_page_size(): void
    {
        $this->assertSame(
            ['clientId' => 12345, 'date' => date('Y-m-d'), 'perPage' => 100],
            $this->request()->pack(),
        );
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
        $packed = $this->request()->setDate('2024-01-02')->setPage(1)->setPerPage(10)->pack();

        $this->assertSame(
            ['clientId' => 12345, 'date' => '2024-01-02', 'page' => 1, 'perPage' => 10],
            $packed,
        );
    }
    private function request(): GetShippingSlip
    {
        return (new GetShippingSlip())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }
}
