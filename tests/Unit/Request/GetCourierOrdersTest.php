<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetCourierOrders;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCourierOrdersTest extends TestCase
{
    private function request(): GetCourierOrders
    {
        return (new GetCourierOrders())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_todays_date_and_the_default_page_size(): void
    {
        // UPGRADE_PLAN §7 #1 — Phase 3: getPerPage() returns the page property; not asserted here.
        $this->assertSame(
            ['clientId' => 12345, 'date' => date('d-m-Y'), 'perPage' => 10],
            $this->request()->pack()
        );
    }

    #[Test]
    public function it_converts_an_iso_date_and_packs_all_filters(): void
    {
        $packed = $this->request()->setDate('2024-01-02')->setPage(2)->setPerPage(20)->pack();

        $this->assertSame(
            ['clientId' => 12345, 'date' => '02-01-2024', 'page' => 2, 'perPage' => 20],
            $packed
        );
    }

    #[Test]
    public function it_keeps_an_already_formatted_date(): void
    {
        $this->assertSame('02-01-2024', $this->request()->setDate('02-01-2024')->pack()['date']);
    }
}
