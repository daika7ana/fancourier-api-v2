<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\CourierOrderTracker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CourierOrderTrackerTest extends TestCase
{
    #[Test]
    public function it_exposes_order_details_and_events(): void
    {
        $tracker = new CourierOrderTracker([
            'orderId' => '18601914',
            'orderNumber' => 'SI329300184',
            'message' => '',
            'events' => [
                ['id' => '0', 'name' => 'In asteptare', 'date' => '2023-11-25 09:00:00'],
                ['id' => '1', 'name' => 'Ridicata', 'date' => '2023-11-25 10:00:00'],
            ],
        ]);

        $this->assertSame('18601914', $tracker->getOrderId());
        $this->assertSame('SI329300184', $tracker->getOrderNo());
        $this->assertSame('', $tracker->getMessage());
        $this->assertCount(2, $tracker->getEvents());
        $this->assertSame(['id' => '1', 'name' => 'Ridicata', 'date' => '2023-11-25 10:00:00'], $tracker->getStatus());
    }

    #[Test]
    public function it_falls_back_to_the_message_when_there_are_no_events(): void
    {
        $tracker = new CourierOrderTracker([
            'orderId' => '18601914',
            'message' => 'Comanda nu a fost gasita',
        ]);

        $this->assertSame('', $tracker->getOrderNo());
        $this->assertSame([], $tracker->getEvents());

        $status = $tracker->getStatus();
        $this->assertNull($status['id']);
        $this->assertSame('Comanda nu a fost gasita', $status['name']);
    }
}
