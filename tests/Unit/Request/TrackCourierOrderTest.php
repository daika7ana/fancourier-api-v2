<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\TrackCourierOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TrackCourierOrderTest extends TestCase
{
    private function request(): TrackCourierOrder
    {
        return (new TrackCourierOrder())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_order_list_by_default(): void
    {
        $this->assertSame(['clientId' => 12345, 'orderId' => []], $this->request()->pack());
    }

    #[Test]
    public function it_packs_all_added_orders_and_the_language(): void
    {
        $request = $this->request()->addOrder('O1')->setOrder('O2')->setLanguage('ro');

        $this->assertSame(
            ['clientId' => 12345, 'orderId' => ['O1', 'O2'], 'language' => 'ro'],
            $request->pack()
        );
    }

    #[Test]
    public function it_can_reset_the_order_list(): void
    {
        $request = $this->request()->addOrder('O1');

        $this->assertSame(['clientId' => 12345, 'orderId' => []], $request->resetOrders()->pack());
    }
}
