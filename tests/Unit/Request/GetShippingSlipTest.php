<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetShippingSlip;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetShippingSlipTest extends TestCase
{
    private function request(): GetShippingSlip
    {
        return (new GetShippingSlip())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_todays_date_and_the_default_page_size(): void
    {
        // UPGRADE_PLAN §7 #1 — Phase 3: getPerPage() returns the page property; not asserted here.
        $this->assertSame(
            ['clientId' => 12345, 'date' => date('Y-m-d'), 'perPage' => 100],
            $this->request()->pack()
        );
    }

    #[Test]
    public function it_packs_all_populated_filters(): void
    {
        $packed = $this->request()->setDate('2024-01-02')->setPage(1)->setPerPage(10)->pack();

        $this->assertSame(
            ['clientId' => 12345, 'date' => '2024-01-02', 'page' => 1, 'perPage' => 10],
            $packed
        );
    }
}
