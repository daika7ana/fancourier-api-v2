<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetBankTransfers;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetBankTransfersTest extends TestCase
{
    private function request(): GetBankTransfers
    {
        return (new GetBankTransfers())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
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
        $packed = $this->request()->setDate('2024-01-02')->setPage(3)->setPerPage(50)->pack();

        $this->assertSame(
            ['clientId' => 12345, 'date' => '2024-01-02', 'page' => 3, 'perPage' => 50],
            $packed
        );
    }

    #[Test]
    public function it_caps_the_page_size_at_one_hundred(): void
    {
        $packed = $this->request()->setDate('2024-01-02')->setPerPage(500)->pack();

        $this->assertSame(100, $packed['perPage']);
    }
}
